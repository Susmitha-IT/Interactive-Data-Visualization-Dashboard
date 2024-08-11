
<style>
        .bottom-right-label {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 5px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
    <script src="https://cdn.amcharts.com/lib/4/core.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/maps.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/geodata/worldLow.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>

            <div class="card">
                <div class="card-header">
                    Impact and Intensity by Regions
                </div>
                <div class="card-body">
                    <div id="chartContainer">
                        <div id="chartdiv"></div>
                        <button id="intensityButton" class="button">Intensity</button>
                        <button id="impactButton" class="button" style="background-color: #EE82EE;">Impact</button>  
                        <!-- Labels for World and Antarctica -->
                        <div id="worldLabel" class="bottom-right-label"></div>
                        <div id="antarcticaLabel" class="bottom-right-label" style="bottom: 40px;"></div>
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

            // Set map definition
            chart.geodata = am4geodata_worldLow;

            // Set projection
            chart.projection = new am4maps.projections.Miller();

            // Create map polygon series
            var polygonSeries = chart.series.push(new am4maps.MapPolygonSeries());
            polygonSeries.exclude = ["AQ"];
            polygonSeries.useGeodata = true;
            polygonSeries.nonScalingStroke = true;
            polygonSeries.strokeWidth = 0.5;
            polygonSeries.calculateVisualCenter = true;

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
                "min": 2,
                "max": 15,
                "dataField": "value"
            });

            intensityTemplate.adapter.add("latitude", function(latitude, target) {
                var polygon = polygonSeries.getPolygonById(target.dataItem.dataContext.id);
                if (polygon) {
                    return polygon.visualLatitude;
                }
                return latitude;
            });

            intensityTemplate.adapter.add("longitude", function(longitude, target) {
                var polygon = polygonSeries.getPolygonById(target.dataItem.dataContext.id);
                if (polygon) {
                    return polygon.visualLongitude;
                }
                return longitude;
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
                "min": 2,
                "max": 15,
                "dataField": "value"
            });

            impactTemplate.adapter.add("latitude", function(latitude, target) {
                var polygon = polygonSeries.getPolygonById(target.dataItem.dataContext.id);
                if (polygon) {
                    return polygon.visualLatitude;
                }
                return latitude;
            });

            impactTemplate.adapter.add("longitude", function(longitude, target) {
                var polygon = polygonSeries.getPolygonById(target.dataItem.dataContext.id);
                if (polygon) {
                    return polygon.visualLongitude;
                }
                return longitude;
            });

            // Define region codes using ISO country codes
            var regionCodeMap = {
                "Northern Europe": "GB", // United Kingdom
                "Oceania": "AU", // Australia
                "Asia": "CN", // China
                "Northern America": "US", // United States
                "Western Asia": "SA", // Saudi Arabia
                "South-Eastern Asia": "ID", // Indonesia
                "Eastern Asia": "JP", // Japan
                "Central Asia": "KZ", // Kazakhstan
                "Southern Asia": "IN", // India
                "Southern Europe": "IT", // Italy
                "Eastern Europe": "RU", // Russia
                "Europe": "DE", // Germany
                "Western Africa": "NG", // Nigeria
                "South America": "BR", // Brazil
                "Western Europe": "FR", // France
                "Central America": "MX", // Mexico
                "Southern Africa": "ZA", // South Africa
                "Central Africa": "CD", // Democratic Republic of the Congo
                "Eastern Africa": "KE", // Kenya
                "Africa": "NG", // Nigeria as a representative for Africa
                "Antarctica": "AQ" // Antarctica
            };

            // Fetch data from your API
            fetch('/api/region')
                .then(response => response.json())
                .then(data => {
                    // Prepare data for intensity
                    let intensityData = data.map(item => ({
                        id: regionCodeMap[item.region],
                        name: item.region,
                        value: parseFloat(item.avg_intensity)
                    }));

                    // Prepare data for impact
                    let impactData = data.map(item => ({
                        id: regionCodeMap[item.region],
                        name: item.region,
                        value: parseFloat(item.avg_impact)
                    }));

                    // Set data
                    intensitySeries.data = intensityData;
                    impactSeries.data = impactData;

                    // Display the values for World and Antarctica
                    data.forEach(item => {
                        if (item.region === "World") {
                            document.getElementById('worldLabel').innerHTML = 
                                `World- Intensity: ${parseFloat(item.avg_intensity).toFixed(3)}`;
                        } else if (item.region === "Antarctica") {
                            document.getElementById('antarcticaLabel').innerHTML = 
                                `Antarctica- Intensity: ${parseFloat(item.avg_intensity).toFixed(3)}`;
                        }
                    });

                    // Initially show intensity data
                    impactSeries.hide();

                    // Add event listeners for buttons
                    document.getElementById('intensityButton').addEventListener('click', function() {
                        impactSeries.hide();
                        intensitySeries.show();
						 data.forEach(item => {
                        if (item.region === "World") {
                            document.getElementById('worldLabel').innerHTML = 
                                `World- Intensity: ${parseFloat(item.avg_intensity).toFixed(3)}`;
                        } else if (item.region === "Antarctica") {
                            document.getElementById('antarcticaLabel').innerHTML = 
                                `Antarctica- Intensity: ${parseFloat(item.avg_intensity).toFixed(3)}`;
                        }
                    });
                    });

                    document.getElementById('impactButton').addEventListener('click', function() {
                        intensitySeries.hide();
                        impactSeries.show();
						 data.forEach(item => {
                        if (item.region === "World") {
                            document.getElementById('worldLabel').innerHTML = 
                                `World- Impact: ${parseFloat(item.avg_impact).toFixed(3)}`;
                        } else if (item.region === "Antarctica") {
                            document.getElementById('antarcticaLabel').innerHTML = 
                                `Antarctica- Impact: ${parseFloat(item.avg_impact).toFixed(3)}`;
                        }
                    });
                    });
                })
                .catch(error => console.error('Error fetching the data:', error));

            // Add a manual label for the "World" region since it's not part of any specific country
            var worldLabel = chart.createChild(am4core.Label);
            worldLabel.text = ""; // Will be updated with API data
            worldLabel.align = "right";
            worldLabel.valign = "bottom";
            worldLabel.marginRight = 15;
            worldLabel.marginBottom = 15;
            worldLabel.fontSize = 14;
            worldLabel.fill = am4core.color("#000");

            // Add Antarctica manually since it has no country code
            var antarcticaLabel = chart.createChild(am4core.Label);
            antarcticaLabel.text = ""; // Will be updated with API data
            antarcticaLabel.align = "right";
            antarcticaLabel.valign = "bottom";
            antarcticaLabel.marginRight = 15;
            antarcticaLabel.marginBottom = 35;
            antarcticaLabel.fontSize = 14;
            antarcticaLabel.fill = am4core.color("#000");
        }); // end am4core.ready()
    </script>

