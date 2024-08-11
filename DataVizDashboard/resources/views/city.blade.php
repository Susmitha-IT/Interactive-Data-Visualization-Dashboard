
<script src="https://cdn.amcharts.com/lib/4/core.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/maps.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/geodata/worldLow.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>
</head>
<body>

            <div class="card">
                <div class="card-header">
                    Impact and intensity by Cities
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

            var title = chart.titles.create();

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
                "min": 4,
                "max": 30,
                "dataField": "value"
            });

            intensityTemplate.adapter.add("latitude", function(latitude, target) {
                return target.dataItem.dataContext.citylat;
            });

            intensityTemplate.adapter.add("longitude", function(longitude, target) {
                return target.dataItem.dataContext.citylng;
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
                return target.dataItem.dataContext.citylat;
            });

            impactTemplate.adapter.add("longitude", function(longitude, target) {
                return target.dataItem.dataContext.citylng;
            });

            // Fetch data and process
            fetch('/api/city')
                .then(response => response.json())
                .then(data => {
                    // Prepare data for intensity
                    let intensityData = data.map(item => ({
                        id: item.city,
                        name: item.city,
                        value: parseFloat(item.avg_intensity),
                        citylat: parseFloat(item.citylat),
                        citylng: parseFloat(item.citylng)
                    }));

                    // Prepare data for impact
                    let impactData = data.map(item => ({
                        id: item.city,
                        name: item.city,
                        value: parseFloat(item.avg_impact),
                        citylat: parseFloat(item.citylat),
                        citylng: parseFloat(item.citylng)
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
                .catch(error => console.error('Error fetching the data:', error));
        }); // end am4core.ready()
    </script>

