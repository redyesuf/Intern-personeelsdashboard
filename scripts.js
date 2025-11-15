// Fetch employees and populate the table
function fetchEmployees() {
    fetch('get_employees.php')
        .then(response => response.json())
        .then(data => {
            const tableBody = document.querySelector('#attendance-table tbody');
            const deletedTableBody = document.querySelector('#deleted-employees-table tbody');
            tableBody.innerHTML = '';
            deletedTableBody.innerHTML = '';

            data.forEach(employee => {
                const row = document.createElement('tr');

                // Store availability in a data attribute
                row.setAttribute('data-id', employee.id);
                row.setAttribute('data-availability', employee.availability);

                // Preserve 'ziek' status, otherwise set status based on availability
                let status = employee.status;
                if (status !== 'ziek') {
                    const availability = JSON.parse(employee.availability);
                    const today = new Date().toLocaleDateString('en-US', { weekday: 'long' }).toLowerCase();
                    status = availability[today] === 'true' ? 'present' : 'absent';
                }

                // Set initial row background color based on status

                if (employee.isDeleted == 0) {
                    row.innerHTML = `
                    <td><p class="attendance-names" data-id="${employee.id}">${employee.name}</p></td>
                    <td class="location-cell">
                        <span class="location">${getLocationBasedOnStatus(status)}</span>
                    </td>
                    <td class="status-cell" onmouseover="showStatusOptions(this)" onmouseout="hideStatusOptions(this)">
                        <span class="status">${status}</span>
                        <div class="status-options d-none">
                            <button onclick="updateEmployeeStatus(${employee.id}, 'present')">Aanwezig</button>
                            <button onclick="updateEmployeeStatus(${employee.id}, 'absent')">Afwezig</button>
                            <button onclick="updateEmployeeStatus(${employee.id}, 'ziek')">Ziek</button>
                        </div>
                    </td>

                    <td class="actions-cell">
                        <div class="dropdown">
                            <button class="btn btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><button class="dropdown-item" onclick="SoftdeleteEmployee(${employee.id})">
                                    <i class="fa-regular fa-trash-can me-2"></i>Verwijderen
                                </button></li>
                            </ul>
                        </div>
                    </td>
                `;
                    
                    updateRowAppearance(row, status);
                    tableBody.appendChild(row);
                } else {
                    row.innerHTML = `
                        <td>${employee.name}</td>
                        <td>${employee.location}</td>
                        <td>${employee.status}</td> 
                        <td>
                            <button onclick="restoreEmployee(${employee.id})">Herstellen</button>
                            <button onclick="deleteEmployee(${employee.id})">Volledig Verwijderen</button>
                        </td>
                    `;
                    deletedTableBody.appendChild(row);
                }
            });

     
        });
}


// Function to map status to location
function getLocationBasedOnStatus(status) {
    if (status === 'present') return 'Technolab';
    if (status === 'absent') return 'Unknown';
    if (status === 'ziek') return 'Unknown';
    return 'Unknown';
}


// Function to update row appearance dynamically
function updateRowAppearance(row, status) {
    row.classList.remove('table-success', 'table-danger', 'table-warning');
    if (status === 'present') row.classList.add('table-success');
    if (status === 'absent') row.classList.add('table-danger');
    if (status === 'ziek') row.classList.add('table-warning');
}

// Show and hide status options on hover
function showStatusOptions(element) {
    const options = element.querySelector('.status-options');
    if (options) {
        options.classList.remove('d-none');
    }
}

function hideStatusOptions(element) {
    const options = element.querySelector('.status-options');
    if (options) {
        options.classList.add('d-none');
    }
}


// Show and hide location options on hover
function showLocationOptions(element) {
    element.classList.add('hovered');
}

function hideLocationOptions(element) {
    element.classList.remove('hovered');
}



// Update status and location dynamically
function updateEmployeeStatus(id, newStatus) {
    const row = document.querySelector(`[data-id="${id}"]`);
    const statusCell = row.querySelector('.status');
    const locationCell = row.querySelector('.location');

    // Determine availability update based on status
    let updatedAvailability = JSON.parse(row.getAttribute('data-availability'));
    const today = new Date().toLocaleDateString('en-US', { weekday: 'long' }).toLowerCase();

    updatedAvailability[today] = (newStatus === 'present') ? 'true' : 'false';

    // Update UI immediately
    statusCell.textContent = newStatus;
    locationCell.textContent = getLocationBasedOnStatus(newStatus);
    updateRowAppearance(row, newStatus);
    
    // Prepare data for the database update
    const formData = new FormData();
    formData.append('id', id);
    formData.append('status', newStatus);
    formData.append('location', getLocationBasedOnStatus(newStatus));
    formData.append('availability', JSON.stringify(updatedAvailability));

    fetch('update_status.php', {
        method: 'POST',
        body: formData
    }).then(response => response.text())
      .then(message => console.log(message));
}



// Delete an employee
function SoftdeleteEmployee(id) {
    const formData = new FormData();
    formData.append('id', id);
    

    fetch('softdelete_employee.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(message => {
        console.log(message);
        showToast('Medewerker succesvol verwijderd!', 'success');
        fetchEmployees(); // Refresh the list
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Fout bij het verwijderen van de medewerker.', 'error');
    });
}


let deleteTargetId = null;

// Original delete function modified to use modal
function deleteEmployee(id) {
    deleteTargetId = id;
    const modal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
    modal.show();
}

// Confirm deletion handler
document.getElementById('confirmDelete').addEventListener('click', function() {
    if (!deleteTargetId) return;

    const formData = new FormData();
    formData.append('id', deleteTargetId);

    fetch('delete_employee.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(message => {
        console.log(message);
        showToast('Medewerker verwijderd uit Database!', 'success');
        fetchEmployees(); // Refresh the list
        bootstrap.Modal.getInstance(document.getElementById('deleteConfirmationModal')).hide();
    })
    .catch(error => {
        showToast('Fout bij het verwijderen van de medewerker.', 'error');
    })
    .finally(() => {
        deleteTargetId = null; // Reset the target ID
    });
});



// Add new employee
document.getElementById('add-employee-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const name = document.getElementById('name').value;
    const role = document.getElementById('role').value; 
    const email = document.getElementById('email').value;
    const additional_info = document.getElementById('bio').value;
    const profilePicture = document.getElementById('profile_picture').files[0];

    // Get availability from checkboxes
    const availability = {
        monday: document.getElementById('monday').checked ? 'true' : 'false',
        tuesday: document.getElementById('tuesday').checked ? 'true' : 'false',
        wednesday: document.getElementById('wednesday').checked ? 'true' : 'false',
        thursday: document.getElementById('thursday').checked ? 'true' : 'false',
        friday: document.getElementById('friday').checked ? 'true' : 'false',
    };
    const availability_json = JSON.stringify(availability);

    const formData = new FormData();
    formData.append('name', name);
    formData.append('role', role);
    formData.append('email', email)
    formData.append('bio', additional_info);
    formData.append('profile_picture', profilePicture);
    formData.append('availability', availability_json); // Send JSON version of availability

    fetch('add_employee.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(message => {
        console.log(message);
        showToast('Medewerker toegevoegd!', 'success');
        fetchEmployees(); // Refresh the list
        document.getElementById('add-employee-form').reset();// Clear the input field

    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Fout bij het toevoegen van de medewerker.', 'error');
    });
});


// Restore Employee
function restoreEmployee(id) {
    const formData = new FormData();
    formData.append('id', id);

    fetch('restore_employee.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(message => {
        console.log(message);
        showToast('Medewerker hersteld!', 'success');
        fetchEmployees(); // Refresh the list
    });
}
// Load employees when the page loads
window.onload = fetchEmployees;


function filterSystem() {
    const searchQuery = document.getElementById('search-bar').value.trim().toLowerCase();
    const statusFilter = document.getElementById('filter-status').value.toLowerCase();
    const locationFilter = document.getElementById('filter-location').value.toLowerCase();

    const rows = document.querySelectorAll('#attendance-table tbody tr');

    rows.forEach(row => {
        const name = row.querySelector('.attendance-names').textContent.trim().toLowerCase();
        const status = row.querySelector('.status').textContent.trim().toLowerCase();
        const location = row.querySelector('.location').textContent.trim().toLowerCase();

        const matchesSearch = !searchQuery || name.includes(searchQuery);
        const matchesStatus = !statusFilter || status === statusFilter;
        const matchesLocation = !locationFilter || location === locationFilter;

        row.style.display = (matchesSearch && matchesStatus && matchesLocation) ? '' : 'none';
    });
}





class Modalfunctions {
    constructor(modalID, modalOpenID, modalExitID) {
        this.modalID = modalID;
        this.modalExitID = modalExitID;
        this.modalOpenID = modalOpenID;
        this.isClosed = true;

        this.Openmodal = this.Openmodal.bind(this);
        this.closeModal = this.closeModal.bind(this);
        this.singlebuttonOpenClose = this.singlebuttonOpenClose.bind(this);
    }

    initializeEventHandlers() {
        if (this.modalOpenID == this.modalExitID) {
            document.getElementById(this.modalOpenID).addEventListener("click", this.singlebuttonOpenClose);
        }else {
        document.getElementById(this.modalOpenID).addEventListener("click", this.Openmodal);
        document.getElementById(this.modalExitID).addEventListener("click", this.closeModal);
        }
    }


    Openmodal() {
        if  (document.getElementById(this.modalID)) {
            const modal = document.getElementById(this.modalID);
            modal.style.display = "block";
        }else {
            console.error(`Modal with ID ${this.modalID} not found.`);
            console.log(this.modalID);
            return;
        }

    }

    closeModal() {
        const modal = document.getElementById(this.modalID);
        modal.style.display = "none";
    }

    singlebuttonOpenClose() {
        if (this.isClosed === true) {
            this.Openmodal();
            this.isClosed = false;
        } else {
            this.closeModal();
            this.isClosed = true;
        }

    }
}

const employeeModalFunctions = new Modalfunctions('add-employee-modal', 'open-employee-modal', 'employee-X');
const filterModalFunctions = new Modalfunctions('filter-modal', 'open-filter-modal', 'filter-X');
const DeletedEmployeeFunctions = new Modalfunctions('deleted-employee-modal', 'deleted-people-button', 'deleted-people-button');
DeletedEmployeeFunctions.initializeEventHandlers();
employeeModalFunctions.initializeEventHandlers();
filterModalFunctions.initializeEventHandlers();



// showToast function
function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    const toastContainer = document.getElementById('toast-container');
    toastContainer.appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast, {
        autohide: true,
        delay: 3000
    });
    bsToast.show();
}




// Pop-up Profile

let currentEmployeeId = null;
let isEditMode = false;

function toggleEditMode() {
isEditMode = !isEditMode;
const saveButton = document.getElementById('saveButton');
const editButton = document.querySelector('.btn-primary'); 

saveButton.classList.toggle('d-none');
editButton.classList.toggle('d-none');

// Toggle editable fields
const fields = {
'profile-name': 'text',
'profile-role': 'text',
'profile-email': 'email',
'profile-bio': 'text'
};

Object.entries(fields).forEach(([id, type]) => {
const element = document.getElementById(id);
const currentValue = element.textContent;

if (isEditMode) {
    element.innerHTML = `<input type="${type}" class="form-control" value="${currentValue}">`;
} else {
    element.textContent = element.querySelector('input').value;
}
});

// Toggle profile picture upload
const profileImage = document.getElementById('profile-image');

if (isEditMode) {
const uploadInput = document.createElement('input');
uploadInput.type = 'file';
uploadInput.id = 'profile-picture-input';
uploadInput.accept = 'image/*';
uploadInput.classList.add('form-control', 'mt-2');
profileImage.parentNode.insertBefore(uploadInput, profileImage.nextSibling);
} else {
const uploadInput = document.getElementById('profile-picture-input');
if (uploadInput) uploadInput.remove();
}

// Toggle availability checkboxes
const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
days.forEach(day => {
const statusElement = document.querySelector(`#profile-availability #${day} .availability-status`);
if (isEditMode) {
    const isAvailable = statusElement.textContent === 'Available';
    statusElement.innerHTML = `<input type="checkbox" class="form-check-input" ${isAvailable ? 'checked' : ''}>`;
} else {
    const checkbox = statusElement.querySelector('input');
    const isChecked = checkbox.checked;
    statusElement.textContent = isChecked ? 'Available' : 'Unavailable';
    statusElement.className = 'availability-status badge ' + (isChecked ? 'bg-success' : 'bg-danger');
}
});
}

function saveChanges() {
const formData = new FormData();
formData.append('id', currentEmployeeId);

// Get values from inputs
formData.append('name', document.getElementById('profile-name').querySelector('input').value);
formData.append('role', document.getElementById('profile-role').querySelector('input').value);
formData.append('email', document.getElementById('profile-email').querySelector('input').value);
formData.append('bio', document.getElementById('profile-bio').querySelector('input').value);

// Get availability
const availability = {};
['monday', 'tuesday', 'wednesday', 'thursday', 'friday'].forEach(day => {
const checkbox = document.querySelector(`#profile-availability #${day} .availability-status input`);
availability[day] = checkbox.checked ? 'true' : 'false';
});
formData.append('availability', JSON.stringify(availability));

 // Determine status based on availability for today
 const today = new Date().toLocaleDateString('en-US', { weekday: 'long' }).toLowerCase();
 const newStatus = availability[today] === 'true' ? 'present' : 'absent';
 formData.append('status', newStatus);
 formData.append('location', getLocationBasedOnStatus(newStatus));

// Handle profile picture if changed
const newProfilePic = document.getElementById('profile-picture-input')?.files[0];
if (newProfilePic) {
formData.append('profile_picture', newProfilePic);
}

fetch('update_employee_profile.php', {
method: 'POST',
body: formData
})
.then(response => response.json())
.then(data => {
if (data.success) {
    showToast('Profile updated successfully!', 'success');
    showProfile(currentEmployeeId); // Refresh the profile display
    fetchEmployees(); // Refresh the main table
    toggleEditMode(); // Toggle back to view mode
    closePopup();
} else {
    showToast('Error updating profile', 'error');
}
});


}




// Update your existing closePopup function
function closePopup() {
const popup = document.getElementById('profilePopup');
popup.style.display = 'none';

// Reset edit mode if active
if (isEditMode) {
toggleEditMode();
}
currentEmployeeId = null;
}





document.addEventListener('click', function(e) {
if (e.target.classList.contains('attendance-names')) {
const employeeId = e.target.getAttribute('data-id');
showProfile(employeeId);
}
});

function showProfile(employeeId) {
// Make an AJAX request to fetch employee details
currentEmployeeId = employeeId;
fetch(`get_employee_profile.php?id=${employeeId}`)
.then(response => response.json())
.then(data => {
    if (data.error) {
        console.error('Error:', data.error);
        alert('Error fetching employee details: ' + data.error);
        return;
    }

    const availability = JSON.parse(data.availability);

    // Check if the elements exist before trying to access them
    const profileName = document.getElementById('profile-name');
    const profileRole = document.getElementById('profile-role');
    const profileBio = document.getElementById('profile-bio');
    const profileEmail = document.getElementById('profile-email');
    const profileImage = document.getElementById('profile-image');
    const badgeContainer = document.getElementById('profile-badges'); // Badge container for multiple badges
    const profileAvailability = document.getElementById('profile-availability');

    if (profileName && profileRole && profileBio && profileEmail && profileImage && badgeContainer) {
        // Populate the pop-up with employee details
        profileName.textContent = data.name || 'No name available';
        profileRole.textContent = data.role || 'No role available';
        profileBio.textContent = data.bio || 'No additional info available';
        profileEmail.textContent = data.email || 'No email available';
        profileImage.src = data.profile_picture && data.profile_picture.trim()
        ? `uploads/profile_pictures/${data.profile_picture}`
        : 'uploads/profile_pictures/default_profile.png';


        // **Clear and update multiple badges**
        badgeContainer.innerHTML = ''; // Clear existing badges
        data.badges.forEach(badge => {
            const badgeImage = document.createElement('img');

            //TODO Initialise the tooltip functions here when i add them
            badgeImage.src = `${badge.badge_image}`;
            badgeImage.alt = badge.badge_name;
            badgeImage.id = badge.badge_name;
            badgeImage.classList.add('img-thumbnail', 'me-2'); // Styling classes
            badgeImage.style.width = '50px'; // Adjust width as needed
            badgeContainer.appendChild(badgeImage);
            DisplayToolTip(badge.badge_name, "badge-tooltip", badge.badge_name)
        });

        // **Update availability background colors and text**
        const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

        days.forEach(day => {
            const dayElement = document.querySelector(`#profile-availability #${day} .availability-status`);

            if (dayElement) {
                // Clear previous styles and text
                dayElement.textContent = '';
                dayElement.className = 'availability-status';

                if (availability[day] === 'true') {
                    dayElement.textContent = 'Available';
                    dayElement.classList.add('badge', 'bg-success');
                } else {
                    dayElement.textContent = 'Unavailable';
                    dayElement.classList.add('badge', 'bg-danger');
                }
            } else {
                console.warn(`Element for ${day} not found`);
            }
        });

    } else {
        console.error('Pop-up elements not found in the DOM');
    }

    // Show the pop-up
    const popup = document.getElementById('profilePopup');
    if (popup) {
        popup.style.display = 'block';
    } else {
        console.error('Pop-up container not found in the DOM');
    }
})
.catch(error => {
    console.error('Error:', error);
});
}

function closePopup() {
document.getElementById('profilePopup').style.display = 'none';
}

//Function for the modal
function DisplayToolTip(InfoIconID,TooltipID,TooltipText) {
    if (!document.getElementById(TooltipID)) {
        const tooltipSpan = document.createElement("p");
        tooltipSpan.classList.add('custom_tooltip')
        tooltipSpan.id = TooltipID;
        
        document.body.appendChild(tooltipSpan);
    }else {
        console.log("Tooltip already exists!");
    }



    let InfoIcon = document.getElementById(InfoIconID);
    
    let Tooltip = document.getElementById(TooltipID);


    InfoIcon.addEventListener('mouseover', (event) => {
        const badgeBox = event.target.getBoundingClientRect();
        Tooltip.style.display = 'block';
        Tooltip.textContent = TooltipText;
        Tooltip.style.left = `${badgeBox.right + 5}px`;
        Tooltip.style.top = `${badgeBox.top}px`;
    });

    InfoIcon.addEventListener('mouseleave', () => {
        Tooltip.style.display = 'none';
    });
}