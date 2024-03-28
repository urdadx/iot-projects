<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>DHT SENSOR DATA READINGS</title>
	<script defer src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
	<style rel="stylesheet">
		* {
			font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
		}

		.heading {
			font-size: 1.5rem;
			text-align: center;
			color: #3b82f6;
			margin-top: 1.5rem;
			margin-bottom: 1rem;
			font-weight: 600;
		}

		.flex-container {
			display: flex;
			margin: 0 auto;
			width: 600px;
			justify-content: center;
			align-items: center;
			gap: 0.75rem;
			margin-bottom: 1.25rem;
		}

		.flex-item {
			display: flex;
			align-items: center;
			gap: 0.5rem;
		}

		.mode-text {
			color: #054d11da;
			;
			font-weight: 600;
			width: 300px;
		}

		/* Select menu styles */
		.select-menu {
			color: rgba(0, 0, 0, 0.7);
			background-color: #fff;
			padding: 0.65rem 0.75rem;
			transition: all 0.3s;
			font-size: 15px;
			cursor: pointer;
			border: 0.5px solid #93c5fd;
			border-radius: 0.45rem;
			outline: 1.5px solid rgba(0, 128, 255, 0.3);
			appearance: none;
			width: 100%;
		}

		/* Button styles */
		.button {
			background-color: #3b82f6;
			color: #fff;
			outline: none;
			border: none;
			padding: 0.75rem 1rem;
			border-radius: 0.375rem;
			transition: background-color 0.3s;
			cursor: pointer;
			font-size: 15px;
		}

		.button:hover {
			background-color: #2563eb;
		}

		/* Mode info styles */
		.mode-info {
			text-align: center;
		}

		.mode-info span {
			font-weight: 600;
		}

		#pump-off {
			background-color: rgb(235, 90, 90);
			color: #fff;
			outline: none;
			border: none;
			padding: 0.75rem 1rem;
			border-radius: 0.375rem;
			border-radius: 0.375rem;
			transition: background-color 0.3s;
			cursor: pointer;
		}

		#pump-off:hover {
			background-color: rgb(236, 121, 121);
		}

		/* Toast container styles */
		.toast-container {
			position: fixed;
			bottom: 20px;
			right: 20px;
			z-index: 9999;
			pointer-events: none;
		}

		/* Toast container */
		.toast-container {
			position: fixed;
			bottom: 20px;
			right: 20px;
			z-index: 9999;
			pointer-events: none;
		}

		/* Toast */
		.toast {
			background-color: #333;
			color: #fff;
			padding: 10px 20px;
			margin-bottom: 10px;
			border-radius: 5px;
			opacity: 0;
			transition: opacity 0.3s ease-in-out;
		}

		/* Toast animation */
		.toast.slide-in {
			opacity: 1;
		}

		.toast.slide-out {
			opacity: 0;
		}

		/* wrapper */
		.wrapper {
			display: flex;
			gap: 20px;
			width: 500px;
			margin: 10px auto;
			justify-content: center;
		}

		/* table */
		.table-container {
			border: 1px solid #e8e7e7;
			border-radius: 11px;
			box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px;
			width: 800px;
		}

		table {
			border-collapse: collapse;
			width: 100%;
		}

		/* table-border */
		tbody tr:not(:last-child) {
			border-bottom: 1px solid #e8e7e7;
		}

		tbody tr td:not(:last-child) {
			border-left: 1px solid #e8e7e7;
		}

		/* style tr, td, th */
		tr th {
			font-size: 14px;
			font-weight: 500;
			color: #fff;
			background-color: #0066ff;
		}

		tr td {
			color: #606060;
			font-size: 14px;
			font-weight: 400;
		}

		th,
		td {
			padding: 16px;
			text-align: center;
		}

		/* even and odd color */
		tbody tr:nth-child(odd) {
			background-color: #fff;
		}

		tbody tr:nth-child(even) {
			background-color: #f8f8f8;
		}

		/* media query */
		@media only screen and (max-width: 600px) {
			.table-container {
				width: 100vw;
				overflow-x: auto;
			}
		}
	</style>

</head>

<body>
	<h1 class="heading">ESP32 DEEP SLEEP LAB 4</h1>
	<div class="flex-container">
		<div class="flex-item">
			<p class="mode-text">Toggle sleep modes:</p>
			<!-- Select menu -->
			<select id="select-mode" name="power-mode" class="select-menu">
				<option value="" disabled selected>Select sleep mode</option>
				<option value="2">Deep Sleep</option>
				<option value="1">Light Sleep</option>
			</select>
		</div>
	</div>

	<div class="flex-container">
		<p class="mode-info">Current sleep mode: <span id='system-mode' style="color:#3b82f6; font-weight: bold;"></span></p>

	</div>
	<div class="wrapper">
		<div class="table-container">
			<table id="dataTable">
				<!-- thead -->
				<thead>
					<tr>
						<th>ID</th>
						<th>Datetime</th>
						<th>Temp</th>
						<th>Humi</th>

					</tr>
				</thead>
				<!-- tbody -->
				<tbody id='dataTable'>
				</tbody>
			</table>
		</div>
	</div>
	<!-- TOAST CONTAINER -->
	<div class="toast-container" id="toastContainer"></div>

</body>

<script>
	// custom toast library
	const showToast = (text, color) => {
		const toastContainer = document.getElementById('toastContainer');
		// Create toast element
		const toast = document.createElement('div');
		toast.classList.add('toast');
		toast.style.backgroundColor = color;
		toast.innerText = text;

		// Append toast to container
		toastContainer.appendChild(toast);

		setTimeout(() => {
			toast.classList.add('slide-in');
		}, 100);

		// Remove toast after 3 seconds
		setTimeout(() => {
			// Trigger slide-out animation
			toast.classList.remove('slide-in');
			toast.classList.add('slide-out');
			setTimeout(() => {
				toast.remove();
			}, 300);
		}, 3000);
	}

	const fetchData = () => {
		const url = `http://localhost/<folder_path>/backend.php?query=last10`;

		const xhr = new XMLHttpRequest();
		xhr.open("GET", url);
		xhr.setRequestHeader("Content-Type", "application/json");

		xhr.onload = () => {
			if (xhr.status === 200) {
				showToast("Fetching data", "#3d97eb");
				const data = JSON.parse(xhr.responseText);
				const tableBody = document.querySelector("#dataTable tbody");
				tableBody.innerHTML = "";

				if (data === null) {
					showToast("Data is NULL", "#f37067");
				}

				data.forEach((item) => {
					const row = document.createElement("tr");
					row.className = "border-b border-blue-gray-200";
					row.innerHTML = `
                    <td>${item.id}</td>
                    <td>${item.timestamp}</td>
                    <td>${item.temperature}</td>
                    <td>${item.humidity}</td>
                `;
					tableBody.appendChild(row);
				});
			} else {
				showToast("Network response was not ok", "#f37067");
			}
		};

		xhr.onerror = () => {
			showToast("Request failed", "#f37067");
		};

		xhr.send();
	};
	fetchData();
	setInterval(fetchData, 3000)
	document.addEventListener('DOMContentLoaded', () => {
		//  Functioo update UI with current power mode
		const updatePowerModeUI = (mode) => {
			document.querySelector("#system-mode").innerText = mode;
			const selectElement = document.querySelector("#select-mode");
			const options = selectElement.options;
			for (let i = 0; i < options.length; i++) {
				if (options[i].value === mode) {
					options[i].selected = true;
					break;
				}
			}
		};

		// Fetch last power mode when the page loads
		const getLastPowerMode = () => {
			const xhr = new XMLHttpRequest();
			xhr.open("GET", 'http://localhost/<folder_path>/backend.php?query=preferences');
			xhr.setRequestHeader("Content-Type", "application/json");

			xhr.onload = () => {
				if (xhr.status === 200) {
					const data = JSON.parse(xhr.responseText);
					if (data.length > 0) {
						const currentPowerMode = data[0].sleepType;
						// updatePowerModeUI(currentPowerMode);
						document.querySelector("#system-mode").innerText = currentPowerMode;

					}
				} else {
					showToast("Network response was not ok", "#f37067");
				}
			};

			xhr.onerror = () => {
				showToast("Fetching last power mode failed", "#f37067");
			};

			xhr.send();
		};

		// Call getLastPowerMode to fetch and display the initial power mode
		getLastPowerMode();

		// Event listener for changing power mode
		document.querySelector("select[name='power-mode']").addEventListener('change', () => {
			const power_mode = document.querySelector("select[name='power-mode']").value;
			const xhr = new XMLHttpRequest();
			xhr.open("POST", 'http://localhost/<folder_path>/backend.php?query=preferences');
			xhr.setRequestHeader("Content-Type", "application/json");

			xhr.onload = () => {
				if (xhr.status === 200) {
					showToast("Power mode changed successfully", "#3d97eb");
					// Update UI with the new power mode
					updatePowerModeUI(power_mode);
					document.querySelector("#system-mode").innerText = currentPowerMode;

				} else {
					showToast("Network response was not ok", "#f37067");
				}
			};

			xhr.onerror = () => {
				showToast("Changing modes failed", "#f37067");
			};

			xhr.send(JSON.stringify({
				sleepType: power_mode
			}));
		});
	});
</script>

</html>