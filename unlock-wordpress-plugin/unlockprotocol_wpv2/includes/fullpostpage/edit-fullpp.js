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
        const lockDiv = document.createElement('div');
        lockDiv.classList.add('lock-item');
    
        const networkLabel = createNetworkLabel();
        lockDiv.appendChild(networkLabel);
    
        const networkSelect = createNetworkSelect(network);
        lockDiv.appendChild(networkSelect);
    
        const lockAddressLabel = createLockAddressLabel();
        lockDiv.appendChild(lockAddressLabel);
    
        const lockAddressInput = createLockAddressInput(lock);
        lockDiv.appendChild(lockAddressInput);
    
        const errorMessage = createErrorMessage();
    
        const removeButton = createRemoveButton(lockDiv);
        lockDiv.appendChild(removeButton);
    
        lockList.appendChild(lockDiv);
    
        removeButton.addEventListener('click', function () {
            lockDiv.remove();
        });
    
        lockAddressInput.addEventListener('input', function () {
            if (!isValidEthereumAddress(lockAddressInput.value)) {
                errorMessage.innerText = 'Lock address is not valid';
                lockDiv.appendChild(errorMessage);
                document.getElementById('publish').setAttribute('disabled', 'disabled');
            } else {
                errorMessage.innerText = '';
                if (errorMessage.parentNode) {
                    errorMessage.parentNode.removeChild(errorMessage);
                }
                document.getElementById('publish').removeAttribute('disabled');
            }
        });

    
        networkSelect.addEventListener('change', function () {
            if (networkSelect.value) {
                lockAddressLabel.style.display = 'block';
                lockAddressInput.style.display = 'block';
            } else {
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
  