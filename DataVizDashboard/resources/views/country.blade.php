
<script src="https://cdn.amcharts.com/lib/4/core.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/maps.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/geodata/worldLow.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>



            <div class="card">
                <div class="card-header">
                    Impact and Intensity by Countries
                </div>
                <div class="card-body">
                    <div id="chartContainer">
                        <div id="chartdiv"></div>
                        <button id="intensityButton" class="button">Intensity</button>
                        <button id="impactButton" class="button" style="background-color: #EE82EE;">Impact</button>  
                    </div>
                </div>
            </div>
       

    <script>
        am4core.ready(function() {
            // Themes begin
            am4core.useTheme(am4themes_animated);
            // Themes end

            // Create map instance
            var chart = am4core.create("chartdiv", am4maps.MapChart);

            chart.geodata = am4geodata_worldLow;

            // Set projection
            chart.projection = new am4maps.projections.Miller();

            // Create map polygon series
            var polygonSeries = chart.series.push(new am4maps.MapPolygonSeries());
            polygonSeries.exclude = ["AQ"];
            polygonSeries.useGeodata = true;
            polygonSeries.nonScalingStroke = true;
            polygonSeries.strokeWidth = 0.5;

            // Create image series for intensity
            var intensitySeries = chart.series.push(new am4maps.MapImageSeries());
            intensitySeries.dataFields.value = "value";

            var intensityTemplate = intensitySeries.mapImages.template;
            intensityTemplate.nonScaling = true;

            var intensityCircle = intensityTemplate.createChild(am4core.Circle);
            intensityCircle.fillOpacity = 0.7;
            intensityCircle.fill = am4core.color("green");
            intensityCircle.tooltipText = "{name}: [bold]{value}[/]";

            intensitySeries.heatRules.push({
                "target": intensityCircle,
                "property": "radius",
                "min": 4,
                "max": 30,
                "dataField": "value"
            });

            intensityTemplate.adapter.add("latitude", function(latitude, target) {
                return target.dataItem.dataContext.latitude;
            });

            intensityTemplate.adapter.add("longitude", function(longitude, target) {
                return target.dataItem.dataContext.longitude;
            });

            // Create image series for impact
            var impactSeries = chart.series.push(new am4maps.MapImageSeries());
            impactSeries.dataFields.value = "value";

            var impactTemplate = impactSeries.mapImages.template;
            impactTemplate.nonScaling = true;

            var impactCircle = impactTemplate.createChild(am4core.Circle);
            impactCircle.fillOpacity = 0.7;
            impactCircle.fill = am4core.color("violet");
            impactCircle.tooltipText = "{name}: [bold]{value}[/]";

            impactSeries.heatRules.push({
                "target": impactCircle,
                "property": "radius",
                "min": 4,
                "max": 30,
                "dataField": "value"
            });

            impactTemplate.adapter.add("latitude", function(latitude, target) {
                return target.dataItem.dataContext.latitude;
            });

            impactTemplate.adapter.add("longitude", function(longitude, target) {
                return target.dataItem.dataContext.longitude;
            });

            // Fetch country data and coordinates
            fetch('/api/country')
                .then(response => response.json())
                .then(data => {
                    let countryPromises = data.map(item => fetchCoordinates(item.country));
                    
                    Promise.all(countryPromises)
                        .then(coordinates => {
                            // Map coordinates to country data
                            let intensityData = data.map((item, index) => ({
                                id: item.country,
                                name: item.country,
                                value: parseFloat(item.avg_intensity),
                                latitude: coordinates[index].lat,
                                longitude: coordinates[index].lng
                            }));

                            let impactData = data.map((item, index) => ({
                                id: item.country,
                                name: item.country,
                                value: parseFloat(item.avg_impact),
                                latitude: coordinates[index].lat,
                                longitude: coordinates[index].lng
                            }));

                            // Set data
                            intensitySeries.data = intensityData;
                            impactSeries.data = impactData;

                            // Initially show intensity data
                            impactSeries.hide();

                            // Add event listeners for buttons
                            document.getElementById('intensityButton').addEventListener('click', function() {
                                impactSeries.hide();
                                intensitySeries.show();
                            });

                            document.getElementById('impactButton').addEventListener('click', function() {
                                intensitySeries.hide();
                                impactSeries.show();
                            });
                        })
                        .catch(error => console.error('Error fetching coordinates:', error));
                })
                .catch(error => console.error('Error fetching country data:', error));

            // Fetch coordinates for a country
            function fetchCoordinates(country) {
    const username = 'susmitha_03'; // Replace with your GeoNames username
    const url = `http://api.geonames.org/searchJSON?q=${encodeURIComponent(country)}&maxRows=1&username=${username}`;

    return fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error(`Network response was not ok: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log(`GeoNames API response for ${country}:`, data);
            if (data.geonames && data.geonames.length > 0) {
                const lat = parseFloat(data.geonames[0].lat);
                const lng = parseFloat(data.geonames[0].lng);

                return {
                    lat: lat,
                    lng: lng
                };
            } else {
                console.warn(`No coordinates found for ${country}. Using default coordinates.`);
                return {
                    lat: 0,
                    lng: 0
                };
            }
        })
        .catch(error => {
            console.error(`Error fetching coordinates for ${country}:`, error);
            return {
                lat: 0,
                lng: 0
            };
        });
}


        }); // end am4core.ready()
    </script>
