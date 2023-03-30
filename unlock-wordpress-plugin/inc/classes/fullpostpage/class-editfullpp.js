document.addEventListener('DOMContentLoaded', function () {
   
    // Get the Add Lock button and the lock list container
    const addButton = document.getElementById('add-lock');
    const lockList = document.getElementById('lock-list');
  
    // Add click event listener to the Add Lock button
    addButton.addEventListener('click', function () {
      addLock();
    });
  

    // Add click event listener to the Save Lock button
    const saveButton = document.getElementById('save-lock');
    saveButton.addEventListener('click', function () {
      saveLockAttributes();
    });



    // Creates and returns the network label element
    function createNetworkLabel() {
        const networkLabel = document.createElement('label');
        networkLabel.innerText = 'Network';
        return networkLabel;
    }
    


// Creates and returns the network select element with available options
function createNetworkSelect(network = '') {
    const networkSelect = document.createElement('select');
    networkSelect.classList.add('lock-network');

    // Add a default empty option
    const defaultOption = document.createElement('option');
    defaultOption.value = '';
    defaultOption.text = 'Select a network';
    defaultOption.selected = !network;
    networkSelect.add(defaultOption);

    // Add the available networks as options in the select element
    ethereumNetworks.forEach(function (net) {
        const option = document.createElement('option');
        option.value = net.value;
        option.text = net.name;
        if (network === net.value) {
            option.selected = true;
        }
        networkSelect.add(option);
    });

    // Set the selected network as the default value for the dropdown
    if (network) {
        networkSelect.value = network;
    }

    return networkSelect;
}


    

    // Creates and returns the lock address label element
    function createLockAddressLabel() {
        const lockAddressLabel = document.createElement('label');
        lockAddressLabel.innerText = 'Lock Address';
        lockAddressLabel.style.display = 'none';
        return lockAddressLabel;
    }
    


    // Creates and returns the lock address input element
    function createLockAddressInput(lock = {}) {
        const lockAddressInput = document.createElement('input');
        lockAddressInput.classList.add('lock-address');
        lockAddressInput.type = 'text';
        lockAddressInput.placeholder = 'Lock Address';
        lockAddressInput.style.display = 'none';
        if (lock.lockAddress) {
            lockAddressInput.value = lock.lockAddress;
        }
    
        return lockAddressInput;
    }
    


    // Creates and returns the remove lock button element
    function createRemoveButton(lockDiv) {
        const removeButton = document.createElement('button');
        removeButton.innerText = 'Remove Lock';
        removeButton.type = 'button';

        // Add inline style for the remove lock button
        removeButton.style.backgroundColor = 'red';
        removeButton.style.color = 'white';
    
        removeButton.addEventListener('click', function () {
            lockDiv.remove();
        });
    
        return removeButton
    }



    // Creates and returns the error message container element
    function createErrorMessage() {
        const errorMessage = document.createElement('div');
        errorMessage.classList.add('error-message');
        return errorMessage;
    }


    // Function to create and add a lock item with the provided lock and network
    function addLock(lock = {}, network = '') {

        // Create a new div element for the lock item and add the 'lock-item' class
        const lockDiv = document.createElement('div');
        lockDiv.classList.add('lock-item');
    
        // Create the network label element and append it to the lock div
        const networkLabel = createNetworkLabel();
        lockDiv.appendChild(networkLabel);

        // Add a line break element after the network label
        const lineBreak = document.createElement('br');
        lockDiv.appendChild(lineBreak);
    
        // Create the network dropdown (select) element and append it to the lock div
        const networkSelect = createNetworkSelect(network);
        lockDiv.appendChild(networkSelect);
    
        // Create the lock address label element and append it to the lock div
        const lockAddressLabel = createLockAddressLabel();
        lockDiv.appendChild(lockAddressLabel);
    
        // Create the lock address input element and append it to the lock div
        const lockAddressInput = createLockAddressInput(lock);
        lockDiv.appendChild(lockAddressInput);

        // Show lock address input and label elements if both network and lock address are provided
        if (network && lock.lockAddress) {
            lockAddressLabel.style.display = 'block';
            lockAddressInput.style.display = 'block';
        }        
    
        // Create the error message container element
        const errorMessage = createErrorMessage();
    
        // Create the remove lock button element and append it to the lock div
        const removeButton = createRemoveButton(lockDiv);
        lockDiv.appendChild(removeButton);
    
        // Append the lock div to the lock list container
        lockList.appendChild(lockDiv);
    
        // Add a click event listener to the remove lock button to remove the lock div
        removeButton.addEventListener('click', function () {
            lockDiv.remove();
        });
    
        // Add an input event listener to the lock address input element
        lockAddressInput.addEventListener('input', function () {

            // If the input value is not a valid Ethereum address
            if (!isValidEthereumAddress(lockAddressInput.value)) {

                // Set the error message text and append it to the lock div
                errorMessage.innerText = 'Lock address is not valid';
                lockDiv.appendChild(errorMessage);

                // Disable the publish button
                document.getElementById('publish').setAttribute('disabled', 'disabled');
            } else {

                // If the input value is valid, clear the error message
                errorMessage.innerText = '';

                // Remove the error message element if it has a parent
                if (errorMessage.parentNode) {
                    errorMessage.parentNode.removeChild(errorMessage);
                }

                // Enable the publish button
                document.getElementById('publish').removeAttribute('disabled');
            }
        });

        // Add a change event listener to the network select (dropdown) element
        networkSelect.addEventListener('change', function () {
            if (networkSelect.value) {

                // If a network is selected (value is not empty)
                lockAddressLabel.style.display = 'block';
                lockAddressInput.style.display = 'block';
            } else {

                // If no network is selected, hide the lock address label and input elements
                // and clear the input value
                lockAddressLabel.style.display = 'none';
                lockAddressInput.style.display = 'none';
                lockAddressInput.value = '';
            }
        });
    }
       

    // Function to validate "lock Address" as a valid Ethereum address using regex
    function isValidEthereumAddress(address) {
      const regex = /^0x[a-fA-F0-9]{40}$/;
      return regex.test(address);
    }
  

    /** If unlockAttributes object exists and has both locks and ethereumNetworks properties,
        iterate through the locks array and call addLock() function for each lock
        with its corresponding network from the ethereumNetworks array.
        This populates the lock list with existing lock attributes when the page loads.
    **/
    if (unlockAttributes && unlockAttributes.locks && unlockAttributes.ethereumNetworks) {
      for (let i = 0; i < unlockAttributes.locks.length; i++) {
        addLock(unlockAttributes.locks[i], unlockAttributes.ethereumNetworks[i]);
      }
    }


    //function to save lock attributes to database
    function saveLockAttributes() {
        const lockItems = document.querySelectorAll('.lock-item');
        const lockAttributes = { locks: [], ethereumNetworks: [] };

        lockItems.forEach(lockItem => {
            const networkSelect = lockItem.querySelector('.lock-network');
            const lockAddressInput = lockItem.querySelector('.lock-address');

            lockAttributes.locks.push({ lockAddress: lockAddressInput.value });
            lockAttributes.ethereumNetworks.push(networkSelect.value);
        });

        // Create a feedback element for success or error messages
        const feedback = document.getElementById('feedback');
        feedback.innerText = '';
        feedback.classList.remove('success', 'error');

        // Save the selected network along with the lock address
        fetch(ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                action: 'save_lock_attributes',
                nonce: document.querySelector('#unlock_protocol_meta_nonce').value,
                post_id: document.querySelector('#post_ID').value,
                lock_attributes: JSON.stringify(lockAttributes),
            })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                feedback.innerText = 'Lock attributes saved successfully';
                feedback.classList.add('success');
            } else {
                feedback.innerText = 'Error saving lock attributes';
                feedback.classList.add('error');
            }
        })
        .catch(error => {
            feedback.innerText = 'Error saving lock attributes';
            feedback.classList.add('error');
            console.error('Catch: Error saving lock attributes', error);
        });
}


    


});
  