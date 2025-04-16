<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Video Local Buzz </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- SweetAlert Library -->

</head>

<body>
    <div class="body d-flex py-3">
        <div class="container-xxl">
            <div class="row align-items-center">
                <div class="border-0 mb-4">
                    <div class="card-header py-3 no-bg bg-transparent d-flex align-items-center px-0 justify-content-between border-bottom flex-wrap">
                        <h3 class="fw-bold mb-0">Add Video Management</h3>
                    </div>
                </div>
            </div> <!-- Row end  -->

            <div class="row align-item-center">
                <div class="col-md-12">
                    <div class="card mb-3">
                        <div class="card-body">
                            <form id="video-form">
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-6">
                                        <label for="video-name" class="form-label">Video Name</label>
                                        <input type="text" class="form-control" id="video-name" name="name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="video-link" class="form-label">Video Link</label>
                                        <input class="form-control" type="text" id="video-link" name="link" required>
                                    </div>
                                    <div class=" col-md-6 mb-3">
                                        <label class="form-label">Run Video at:</label>
                                        <div>
                                            <input type="checkbox" id="stateLevel" name="stateLevel" value="1"> State Level
                                            <input type="checkbox" id="districtLevel" name="districtLevel" value="1"> District Level
                                            <input type="checkbox" id="scheduled" name="scheduled" value="1"> Scheduled Time
                                        </div>
                                    </div>
                                    <div class=" col-md-6 mb-3">
                                        <label class="form-label">Scheduled Time</label>
                                        <div>
                                            <input type="datetime-local" id="scheduledTime" class="form-control" name="scheduledTime" required disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="state-select" class="form-label">Select State</label>
                                        <select id="state-select" class="form-select" name="state" required>
                                            <option value="">--Select State--</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="district-select" class="form-label">Select District</label>
                                        <select id="district-select" name="district" class="form-select" disabled required>
                                            <option value="">--Select District--</option>
                                        </select>
                                    </div>
                                    <div id="message-div"></div>

                                    <!-- Display selected districts -->
                                    <div class="col-md-12 mt-3">
                                        <label class="form-label">Selected District</label>
                                        <table id="selected-districts-table" class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>District Name</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- List of selected districts will appear here -->
                                            </tbody>
                                        </table>
                                    </div>
                                    <p id="error-message" class="error"></p>

                                </div>

                                <button type="submit" class="btn btn-primary mt-4">Add Video</button>

                            </form>
                        </div>
                    </div>
                </div>
            </div><!-- Row end  -->
        </div>
    </div>
    <div class="body d-flex py-3">
        <div class="container-xxl">
            <div class="row g-3 mb-3">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-transparent border-bottom-0">
                            <h6 class="m-0 fw-bold">User List</h6>
                        </div>
                        <div class="card-body">
                            <table id="myDataTable" class="table table-hover align-middle mb-0" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th style="text-align: center;">Id</th>
                                        <th style="text-align: center;">Video Name</th>
                                        <th style="text-align: center;">State Name</th>
                                        <th style="text-align: center;">District Name</th>
                                        <th style="text-align: center;">Scheduled Time</th>
                                        <th style="text-align: center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Table body will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div><!-- Row end  -->
        </div>
    </div>

    <script>
        const urls = {
            add_video_link: "<?php echo $add_video_link; ?>",
            delete_video_link: "<?php echo $delete_video_link; ?>",
            get_videos_link: "<?php echo $get_videos_link; ?>"
        };

        // Fetch the states and districts data from a JSON file
        fetch('state.json')
            .then(response => response.json())
            .then(jsonData => {
                const $ = id => document.getElementById(id); // Helper to get elements by ID
                const stateSelect = $('state-select');
                const districtSelect = $('district-select');
                const stateLevelCheckbox = $('stateLevel');
                const districtLevelCheckbox = $('districtLevel');
                const selectedDistrictsTable = $('selected-districts-table').getElementsByTagName('tbody')[0];
                const scheduledCheckbox = $('scheduled');
                const scheduledTimeInput = $('scheduledTime');
                const errorMessage = $('error-message');
                const form = $('video-form');

                // Function to populate dropdown
                const populateDropdown = (selectElement, data, valueKey = 'district_name', textKey = 'district_name') => {
                    selectElement.innerHTML = '<option value="">--Select--</option>'; // Clear existing options
                    if (data) { //Check if Data Is Available
                        data.forEach(item => {
                            const option = document.createElement('option');
                            option.value = item[valueKey];
                            option.textContent = item[textKey];
                            selectElement.appendChild(option);
                        });
                    }
                };

                // Function to display/remove district
                const displaySelectedDistrict = () => {
                    selectedDistrictsTable.innerHTML = '';
                    const selectedDistrictValue = districtSelect.options[districtSelect.selectedIndex]?.text;
                    if (!selectedDistrictValue) return; // Exit if no district selected

                    const newRow = document.createElement('tr');
                    const districtCell = document.createElement('td');
                    districtCell.textContent = selectedDistrictValue;

                    const actionCell = document.createElement('td');
                    const removeBtn = document.createElement('button');
                    removeBtn.textContent = 'Remove';
                    removeBtn.classList.add('btn', 'btn-danger', 'btn-sm');
                    removeBtn.addEventListener('click', () => {
                        newRow.remove();
                        districtSelect.value = ""; // Reset Select Field
                        populateDropdown(districtSelect, [], '', '');
                        districtSelect.disabled = true;
                    });

                    actionCell.appendChild(removeBtn);
                    newRow.appendChild(districtCell);
                    newRow.appendChild(actionCell);
                    selectedDistrictsTable.appendChild(newRow);
                };

                // State Level and District Level Checkbox handlers combined
                const handleLevelCheckboxChange = () => {
                    districtSelect.disabled = !(districtLevelCheckbox.checked && stateSelect.value);
                    stateLevelCheckbox.checked = !districtLevelCheckbox.checked; //Uncheck the other
                    displaySelectedDistrict();
                };

                const populateDistrictBasedOnState = (state) => {
                    if (state) {
                        const selectedState = jsonData.states.find(stateItem => stateItem.state_name === state);
                        if (selectedState) {
                            districtSelect.disabled = false;
                            populateDropdown(districtSelect, selectedState.districts, 'district_name', 'district_name');

                        } else {
                            districtSelect.disabled = true;
                            populateDropdown(districtSelect, [], '', '');
                        }
                    } else {
                        districtSelect.disabled = true;
                        populateDropdown(districtSelect, [], '', '');
                    }
                }
                // State Select Event Listener
                stateSelect.addEventListener('change', (event) => {
                    const selectedStateName = event.target.value;
                    populateDistrictBasedOnState(selectedStateName)
                    handleLevelCheckboxChange();

                });

                // District Select Event Listener
                districtSelect.addEventListener('change', (event) => {
                    displaySelectedDistrict();

                });

                // Add Click Listener to both Level Checkboxes
                stateLevelCheckbox.addEventListener('change', handleLevelCheckboxChange);
                districtLevelCheckbox.addEventListener('change', handleLevelCheckboxChange);

                // Populate states initially
                fetch('state.json')
                    .then(response => response.json())
                    .then(jsonData => {
                        populateDropdown(stateSelect, jsonData.states, 'state_name', 'state_name');

                    });


                // ---  Scheduled Time Logic (Modified Section) ---
                const toggleSchedule = () => {
                    scheduledTimeInput.disabled = !scheduledCheckbox.checked;
                    scheduledTimeInput.required = scheduledCheckbox.checked;

                    // Disable/Enable state and district selections
                    stateSelect.disabled = scheduledCheckbox.checked;
                    districtSelect.disabled = scheduledCheckbox.checked || !districtLevelCheckbox.checked;

                    //Uncheck the other
                    if (scheduledCheckbox.checked) {
                        stateLevelCheckbox.checked = false;
                        districtLevelCheckbox.checked = false;
                    }

                    //Clear Dropdown if Disabled
                    if (stateSelect.disabled) {
                        stateSelect.value = "";
                    }
                    if (districtSelect.disabled) {
                        districtSelect.value = "";
                        selectedDistrictsTable.innerHTML = ''; //Clear the selected District
                    }
                };
                toggleSchedule(); // Set initial state
                scheduledCheckbox.addEventListener('change', toggleSchedule);
                // -------------------------------------------------



                form.addEventListener('submit', function(event) {
                    event.preventDefault(); // Prevent default form submission

                    // Clear previous error messages
                    errorMessage.textContent = '';

                    // Get form data
                    const videoName = $('video-name').value;
                    const videoLink = $('video-link').value;
                    const selectedStateName = stateSelect.value;
                    const selectedDistrictName = districtSelect.options[districtSelect.selectedIndex]?.text;
                    const scheduleTime = scheduledTimeInput.value;

                    let stateId = null;
                    let districtId = null;

                    // Validate the required Checkbox
                    if (!(stateLevelCheckbox.checked || districtLevelCheckbox.checked || scheduledCheckbox.checked)) {
                        errorMessage.textContent = 'Please select at least one Run Video option';
                        return;
                    }

                    // Validate data (basic example)
                    if (!videoName || !videoLink) {
                        errorMessage.textContent = 'Video Name and Video Link are required.';
                        return;
                    }

                    if (scheduledCheckbox.checked && !scheduleTime) {
                        errorMessage.textContent = 'Scheduled Time is required when "Scheduled Time" is checked.';
                        return;
                    }


                    // Find State and District Information
                    if (stateLevelCheckbox.checked && selectedStateName) {
                        const selectedState = jsonData.states.find(state => state.state_name === selectedStateName);
                        if (selectedState) {
                            stateId = selectedState.state_id; // Retrieve state_id
                        } else {
                            errorMessage.textContent = 'Selected state not found in data.';
                            return;
                        }
                    }

                    if (districtLevelCheckbox.checked && selectedDistrictName) {
                        const selectedState = jsonData.states.find(state => state.state_name === selectedStateName);
                        if (selectedState) {
                            const selectedDistrict = selectedState.districts.find(district => district.district_name === selectedDistrictName);
                            if (selectedDistrict) {
                                districtId = selectedDistrict.district_id;
                            }
                        }
                    }


                    // Create payload
                    const payload = {
                        video_name: videoName,
                        video_link: videoLink
                    };

                    if (stateLevelCheckbox.checked && selectedStateName && stateId) {
                        payload.state_id = stateId;
                        payload.state_name = selectedStateName;
                    }

                    if (districtLevelCheckbox.checked && selectedDistrictName && districtId) {
                        payload.district_id = districtId;
                        payload.district_name = selectedDistrictName;
                    }


                    if (scheduledCheckbox.checked && scheduleTime) {
                        payload.schedule_time = scheduleTime;
                    }

                    // Make the API call
                    fetch(urls.add_video_link, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                // Use SweetAlert for success message
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: data.message,
                                }).then(() => {
                                    form.reset(); // Reset the form
                                    selectedDistrictsTable.innerHTML = ''; //Clear Table
                                    toggleSchedule(); // Reset Scheduled time
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: data.message,
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'An error occurred while submitting the form.',
                            });
                        });
                });


            });

        const myHeaders = new Headers();
        myHeaders.append("Content-Type", "application/json");

        const requestOptions = {
            method: "POST",
            headers: myHeaders,
            body: "", // Initially empty, will be set in deleteVideo
            redirect: "follow"
        };

        fetch(urls.get_videos_link, requestOptions)
            .then((response) => response.json())
            .then((result) => {
                const tableBody = document.querySelector("#myDataTable tbody");
                tableBody.innerHTML = '';

                const allVideos = (result.scheduled_videos || []).concat(result.normal_videos || []);

                allVideos.forEach((video) => {
                    const row = document.createElement("tr");

                    // ... (rest of your row creation code remains the same)
                    const idCell = document.createElement("td");
                    idCell.style.textAlign = "center";
                    idCell.textContent = video.id;
                    row.appendChild(idCell);

                    const videoNameCell = document.createElement("td");
                    videoNameCell.style.textAlign = "center";
                    videoNameCell.textContent = video.video_name;
                    row.appendChild(videoNameCell);

                    const stateNameCell = document.createElement("td");
                    stateNameCell.style.textAlign = "center";
                    stateNameCell.textContent = video.state_name || "N/A";
                    row.appendChild(stateNameCell);

                    const districtNameCell = document.createElement("td");
                    districtNameCell.style.textAlign = "center";
                    districtNameCell.textContent = video.district_name || "N/A";
                    row.appendChild(districtNameCell);

                    const scheduleTimeCell = document.createElement("td");
                    scheduleTimeCell.style.textAlign = "center";
                    scheduleTimeCell.textContent = video.schedule_time || "N/A";
                    row.appendChild(scheduleTimeCell);

                    const actionCell = document.createElement("td");
                    actionCell.style.textAlign = "center";


                    // Create the Delete button
                    const deleteButton = document.createElement("button");
                    deleteButton.classList.add("btn", "btn-primary");
                    deleteButton.textContent = "Delete";
                    deleteButton.dataset.videoId = video.video_id; // Use video_id, not id

                    // Add an event listener to the Delete button
                    deleteButton.addEventListener("click", function() {
                        const videoId = this.dataset.videoId;
                        console.log("Delete button clicked for video ID:", videoId);

                        Swal.fire({ //Use SweetAlert for confirmation
                            title: 'Are you sure?',
                            text: `You won't be able to revert this!`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Yes, delete it!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                deleteVideo(videoId, row);
                            }
                        })
                    });

                    actionCell.appendChild(deleteButton);
                    row.appendChild(actionCell);
                    tableBody.appendChild(row);
                });
            })
            .catch((error) => console.error(error));


        function deleteVideo(videoId, rowElement) {
            const deleteHeaders = new Headers();
            deleteHeaders.append("Content-Type", "application/json");

            const deleteRaw = JSON.stringify({
                video_id: videoId
            }); // Use video_id

            const deleteRequestOptions = {
                method: "POST",
                headers: deleteHeaders,
                body: deleteRaw,
                redirect: "follow"
            };

            fetch(urls.delete_video_link, deleteRequestOptions) // Correct endpoint
                .then(response => {
                    if (response.ok) {
                        return response.json(); // Expecting JSON response
                    } else {
                        throw new Error(`Failed to delete video. Status: ${response.status}`);
                    }
                })
                .then(data => {
                    // Check the response data, assuming your API returns { "status": "success" } on success
                    if (data.status === "success") {
                        console.log("Video deleted successfully:", data);
                        // Use SweetAlert for success message
                        Swal.fire(
                            'Deleted!',
                            'Your file has been deleted.',
                            'success'
                        ).then(() => {
                            rowElement.remove(); //Remove Element only after successful Swal.fire
                        })
                    } else {
                        // Handle cases where deletion might have failed on the server side
                        console.error("Video deletion failed:", data);
                        Swal.fire({ //Sweet Alert for Failure
                            icon: 'error',
                            title: 'Oops...',
                            text: `Video deletion failed: ${data.message || 'Unknown error'}`,
                        });
                    }
                })
                .catch(error => {
                    console.error("Error deleting video:", error);
                    Swal.fire({ //Sweet Alert for Error
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Error deleting video. See console for details.',
                    });
                });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
</body>

</html>