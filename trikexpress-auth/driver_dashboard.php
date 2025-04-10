<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard | TrikExpress</title>

    <!-- ✅ Bootstrap & FontAwesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="driver.css">

    <!-- ✅ Google Maps API -->
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBEXe6peKpjp8CBQdylRhzFCvpi2S4522o&libraries=places&callback=initMap"></script>
</head>
<body>

    <!-- ✅ Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="branding"><i class="fas fa-motorcycle"></i> TrikExpress</div>
        <div class="sidebar-links">
            <a href="driver_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <!-- ✅ Burger Menu -->
    <div class="burger-menu" onclick="toggleSidebar()">☰</div>

    <!-- ✅ Main Content -->
    <div class="content">
    <h2 class="text-center text-white">Welcome, <?php echo htmlspecialchars($_SESSION["full_name"] ?? "Driver"); ?>!</h2>

    <!-- ✅ Show Available Rides Button -->
    <button class="btn btn-primary my-3" onclick="showAvailableRides()">Show Available Rides</button>

    <!-- ✅ Available Rides -->
    <div class="rides-container" id="ridesContainer" style="display: none;">
        <h3 class="text-white">Available Rides</h3>
        <div class="table-responsive">
            <table class="table table-dark table-hover">
                <thead>
                    <tr>
                        <th>Passenger</th>
                        <th>Pickup</th>
                        <th>Destination</th>
                        <th>Fare</th>
                        <th>Coordinates</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="rideList">
                    <tr><td colspan="6" class="text-center">Loading rides...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ✅ Ongoing Ride Section -->
    <div id="ongoing-ride" style="display: none;">
        <h3 class="text-white">Ongoing Ride</h3>
        <p><strong>Pickup:</strong> <span id="ongoing-pickup-location"></span></p>
        <p><strong>Drop-off:</strong> <span id="ongoing-dropoff-location"></span></p>
        <p><strong>Fare:</strong> <span id="ongoing-fare-amount"></span></p>

        <!-- ✅ Action Buttons for Ongoing Ride -->
        <button class="btn btn-success" onclick="endRide()">End Ride</button>
        <button class="btn btn-danger" onclick="cancelRideByDriver()">Cancel Ride</button>
    </div>

    <!-- ✅ Ride Request Details -->
    <div id="ride-request" style="display: none;">
        <h3 class="text-white">Ride Details</h3>
        <p><strong>Pickup:</strong> <span id="pickup-location"></span></p>
        <p><strong>Drop-off:</strong> <span id="dropoff-location"></span></p>
        <p><strong>Fare:</strong> <span id="fare-amount"></span></p>
        
        <!-- ✅ Action Buttons -->
        <button class="btn btn-success" onclick="startRide()">Start Ride</button>
        <button class="btn btn-danger" onclick="cancelRideByDriver()">Cancel Ride</button>
    </div>
</div>


        <!-- ✅ Google Maps Navigation -->
        <div class="container map-container" id="mapContainer" style="display: none;">
            <h3 class="text-white">Navigation</h3>
            <div class="controls">
                <input id="pickup" type="text" class="form-control mb-2" placeholder="Pickup Location" readonly />
                <input id="destination" type="text" class="form-control mb-2" placeholder="Destination" readonly />
                <button class="btn btn-primary w-100" onclick="calculateRoute()">Get Directions</button>
            </div>
            <p id="ride-info" class="info text-white"></p>
            <div id="map" style="height: 400px;"></div>
        </div>
    </div>

    <!-- ✅ JavaScript Dependencies -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="driver.js"></script> <!-- ✅ External JavaScript File -->
</body>
</html>
