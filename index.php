<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- SweetAlert Library -->

</head>

<body>
    <!-- Videos will be dynamically added here -->

    <div class="container mx-auto p-4">
        <header class="text-center mb-8">
            <h1 class="text-4xl font-extrabold text-gray-900">
                <span class="text-blue-600">Local</span> Video Buzz
            </h1>
            <p class="text-lg text-gray-600 mt-2">
                Discover videos relevant to your area!
            </p>
        </header>

        <div id="loading" class="text-center text-gray-600 italic mb-4">
            <!-- Loading spinner -->
            <i class="fas fa-spinner fa-spin text-2xl mr-2" style="animation: spin 1s linear infinite;"></i>
            <span>Finding your location...</span>
        </div>

        <div id="error" class="text-red-500 text-center mb-4"></div>


        <!-- Scheduled Videos Section -->
        <section id="scheduledVideoContainer"
            style="margin-bottom: 3rem; display: flex; flex-wrap: wrap; justify-content: center; gap: 20px;"></section>

        <!-- Normal Videos Section -->
        <section id="normalVideoContainer" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 20px;">
        </section>
    </div>

    <script>
        (function () {
            if (!navigator.geolocation) {
                document.getElementById('error').textContent = "Geolocation not supported.";
                document.getElementById('loading').style.display = 'none';
                return;
            }

            navigator.geolocation.getCurrentPosition(success, error, {
                enableHighAccuracy: false,
                timeout: 5000,
                maximumAge: 0
            });

            function success(position) {
                const {
                    latitude,
                    longitude
                } = position.coords;
                const gpsUrl = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}&zoom=18&accept-language=en`;

                fetch(gpsUrl)
                    .then(response => response.json())
                    .then(data => {
                        if (data.address) {
                            const {
                                state_district,
                                state,
                                region,
                                county
                            } = data.address;
                            const district = state_district ? state_district.replace(/ district$/i, '') : '';
                            const finalState = state || region || county || '';
                            document.getElementById('loading').style.display = 'none';
                            fetchVideos(district, finalState);
                        } else {
                            document.getElementById('error').textContent = 'Address lookup failed: ' + (data.error || 'No address found.');
                            document.getElementById('loading').style.display = 'none';
                        }
                    })
                    .catch(err => {
                        document.getElementById('error').textContent = 'Fetch error: ' + err;
                        document.getElementById('loading').style.display = 'none';
                    });
            }

            function error(err) {
                const errors = {
                    1: 'Permission denied',
                    2: 'Position unavailable',
                    3: 'Timeout'
                };
                const errorMessage = errors[err.code] || 'Unknown error';
                document.getElementById('error').textContent = "Error getting location: " + errorMessage;
                document.getElementById('loading').style.display = 'none';
            }

            function fetchVideos(gpsDistrict, gpsState) {
                const myHeaders = new Headers();
                myHeaders.append("Content-Type", "application/json");

                const requestOptions = {
                    method: "POST",
                    headers: myHeaders,
                    body: "",
                    redirect: "follow"
                };

                fetch("getvideo.php", requestOptions)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Network response was not ok: ${response.status} ${response.statusText}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (!data || (!data.scheduled_videos && !data.normal_videos)) {
                            document.getElementById('error').textContent = "API did not return valid video data.";
                            return;
                        }

                        const scheduledVideoContainer = document.getElementById('scheduledVideoContainer');
                        const normalVideoContainer = document.getElementById('normalVideoContainer');
                        let videoFound = false;
                        const now = new Date();
                        let scheduledVideoExists = false;

                        if (data.scheduled_videos && data.scheduled_videos.length > 0) {
                            scheduledVideoExists = true;
                        }

                        // --- Scheduled Videos ---
                        if (data.scheduled_videos) {
                            //Remove "Coming Up Soon" title
                            data.scheduled_videos.forEach(video => {
                                const scheduleTime = new Date(video.schedule_time);
                                if (scheduleTime <= now) {
                                    addVideoToContainer(video, scheduledVideoContainer);
                                    videoFound = true;
                                } else {
                                    addCountdownVideo(video, scheduledVideoContainer, scheduleTime);
                                    videoFound = true;
                                }
                            });
                        }

                        // --- Normal Videos ---
                        if (data.normal_videos) {
                            data.normal_videos.forEach(video => {
                                const videoDistrict = (video.district_name || '').toLowerCase().trim();
                                const videoState = (video.state_name || '').toLowerCase().trim();
                                const currentDistrict = (gpsDistrict || '').toLowerCase().trim();
                                const currentState = (gpsState || '').toLowerCase().trim();

                                if (videoDistrict === currentDistrict || videoState === currentState) {
                                    addVideoToContainer(video, normalVideoContainer, `normal-video-${video.id}`);
                                    videoFound = true;
                                }
                            });
                        }

                        if (!videoFound && !scheduledVideoExists) {
                            normalVideoContainer.innerHTML = '<p class="text-center text-gray-600">No matching videos found for your location.</p>';
                        }
                    })
                    .catch(error => {
                        document.getElementById('error').textContent = 'Error fetching videos: ' + error;
                        document.getElementById('loading').style.display = 'none';
                    });
            }

            function addCountdownVideo(video, videoContainer, scheduleTime) {
                const videoDiv = document.createElement('div');
                videoDiv.style = "background-color: white; border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); padding: 1rem;  display: flex; flex-direction: column; align-items: center; transition: transform 0.2s ease-in-out; width: 100%; box-sizing: border-box;"; // Added width: 100% and box-sizing

                videoDiv.id = `scheduled-video-${video.id}`;

                // Hover effect
                videoDiv.onmouseover = () => {
                    videoDiv.style.transform = "scale(1.03)";
                    videoDiv.style.boxShadow = "0 4px 8px rgba(0, 0, 0, 0.2)";
                };
                videoDiv.onmouseout = () => {
                    videoDiv.style.transform = "scale(1)";
                    videoDiv.style.boxShadow = "0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)";
                };
                const title = document.createElement('h3');
                title.textContent = video.video_name;
                title.style = "font-size: 1.25rem; font-weight: 600; color: #374151; text-align: center;";
                videoDiv.appendChild(title);

                const countdownContainer = document.createElement('div');
                countdownContainer.style = "margin-top: 0.75rem;";
                const countdownElement = document.createElement('p');

                // Countdown button styling
                countdownElement.style = "padding-left: 1.5rem; padding-right: 1.5rem; padding-top: 0.75rem; padding-bottom: 0.75rem; border-radius: 9999px; color: white; font-weight: bold; background: linear-gradient(to right, #6B7280, #4B5563); transition: background 0.3s ease; text-align: center; font-size: 1.5rem;"; // Larger font size

                countdownContainer.appendChild(countdownElement);
                videoDiv.appendChild(countdownContainer);

                // Hover effect for countdown button
                countdownElement.onmouseover = () => {
                    countdownElement.style.background = "linear-gradient(to right, #4B5563, #374151)";
                }
                countdownElement.onmouseout = () => {
                    countdownElement.style.background = "linear-gradient(to right, #6B7280, #4B5563)";
                }

                updateCountdown(countdownElement, scheduleTime);
                const countdownInterval = setInterval(() => {
                    updateCountdown(countdownElement, scheduleTime);
                }, 1000);

                setTimeout(() => {
                    clearInterval(countdownInterval);
                    countdownElement.textContent = "Live Now!";
                    removeAllNormalVideos();
                    addVideoToContainer(video, document.getElementById('scheduledVideoContainer'));
                    videoDiv.remove();
                    removeOtherScheduledCountdowns(video.id);
                }, scheduleTime - new Date());

                videoContainer.appendChild(videoDiv);
            }

            function updateCountdown(element, scheduleTime) {
                const now = new Date();
                const timeLeft = scheduleTime - now;

                if (timeLeft <= 0) {
                    element.textContent = "Video is live!";
                    return;
                }

                const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
                const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

                let countdownString = "Live in: ";
                if (days > 0) countdownString += `${days}d `;
                if (hours > 0) countdownString += `${hours}h `;
                if (minutes > 0) countdownString += `${minutes}m `;
                countdownString += `${seconds}s`;
                element.textContent = countdownString;
            }

            function addVideoToContainer(video, videoContainer, id = null) {
                const videoDiv = document.createElement('div');
                // Removed margin-bottom, let the container's gap handle it
                videoDiv.style = "background-color: white; border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); padding: 1.5rem;  width: 100%; display: flex; flex-direction: column; align-items: center; transition: transform 0.2s ease-in-out; box-sizing: border-box;"; // Added box-sizing

                // Responsive width adjustments for normal videos ONLY
                if (id && id.startsWith("normal-video-")) { // Check if it's a normal video
                    if (window.innerWidth >= 768) { // md: breakpoint and up
                        videoDiv.style.width = "calc(600px - 20px)"; // 50% width, accounting for the gap
                    }
                }


                // Hover effect
                videoDiv.onmouseover = () => {
                    videoDiv.style.transform = "scale(1.03)";
                    videoDiv.style.boxShadow = "0 4px 8px rgba(0, 0, 0, 0.2)";
                };
                videoDiv.onmouseout = () => {
                    videoDiv.style.transform = "scale(1)";
                    videoDiv.style.boxShadow = "0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)";
                };

                if (id) {
                    videoDiv.id = id;
                }

                const videoId = getVideoId(video.video_link);
                if (videoId) {
                    const iframe = document.createElement('iframe');
                    iframe.src = `https://www.youtube.com/embed/${videoId}`;
                    iframe.allowFullscreen = true;
                    iframe.frameBorder = "0";
                    iframe.style = "width: 100%; aspect-ratio: 16 / 9; margin-bottom: 1rem;";
                    videoDiv.appendChild(iframe);
                } else {
                    const invalidLink = document.createElement('p');
                    invalidLink.style = "color: #ef4444; font-size: 0.875rem;";
                    invalidLink.textContent = "Invalid YouTube Link: " + video.video_link;
                    videoDiv.appendChild(invalidLink);
                }

                const title = document.createElement('h3');
                title.textContent = video.video_name;
                title.style = "font-size: 1.25rem; font-weight: 600; color: #374151; margin-top: 0.5rem; text-align:center;";
                videoDiv.appendChild(title);

                videoContainer.appendChild(videoDiv);
            }

            function getVideoId(url) {
                const urlParams = new URLSearchParams(new URL(url).search);
                return urlParams.get('v');
            }

            function removeAllNormalVideos() {
                const normalVideos = document.querySelectorAll('[id^="normal-video-"]');
                normalVideos.forEach(video => video.remove());
            }

            function removeOtherScheduledCountdowns(currentVideoId) {
                const scheduledVideos = document.querySelectorAll('[id^="scheduled-video-"]');
                scheduledVideos.forEach(videoDiv => {
                    if (videoDiv.id !== `scheduled-video-${currentVideoId}`) {
                        videoDiv.remove();
                    }
                });
            }

            // Keyframes for spinner (must be defined in a <style> tag or external CSS)
            const style = document.createElement('style');
            style.innerHTML = `
                    @keyframes spin {
                        0% { transform: rotate(0deg); }
                        100% { transform: rotate(360deg); }
                    }
                `;
            document.head.appendChild(style);

        })();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq"
        crossorigin="anonymous"></script>
</body>

</html>