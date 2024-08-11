<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Global Trend Analysis</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>
<body>
    <div class="container mt-5">
        <div class="chart-container">
            <div class="card">
                <div class="card-header">
                    Pestle Reports
                </div>
                <div class="card-body">
                    <div id="chartContainer">
                    <div id="treemap-container"></div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    Pestle's Intensity by end of the year
                </div>
                <div class="card-body">
                    <div id="chartContainer">
                    <div class="mb-3">
                        <label for="yearSelect" class="form-label">Select End Year</label>
                        <select id="yearSelect" class="form-select">
                        <!-- Options will be populated by JavaScript -->
                        </select>
                    </div>
                    <canvas id="lineChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="container map-container">
            <div class="labels">
                <button class="label-button active" data-include="city">City</button>
                <button class="label-button" data-include="country">Country</button>
                <button class="label-button" data-include="region">Region</button>
            </div>
            <div class="map">
                @include('city') <!-- Default content is City -->
                <br>
            </div>
        </div>
    <div class="chart-container">
            <div class="card">
                <div class="card-header">
                    Sectors across the world
                </div>
                <div class="card-body">
                    <div id="chartContainer">
                        <canvas id="mixedChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    SWOT Analysis by Pestle
                </div>
                <div class="card-body">
                    <div id="chartContainer">
                        <div id="chart"></div>
                    </div>
                </div>
            </div>
    </div>
    <div class="container map-container">
    <div class="card">
                <div class="card-header">
                    Pestle sources and topics
                </div>
                <div class="card-body">
                    <div id="chartContainer">
                    <label for="pestle-select">Select Pestle:</label>
    <select id="pestle-select" onchange="updateChart()">
        <!-- Options will be dynamically populated -->
    </select>
    
    <canvas id="pestleChart"></canvas>
                    </div>
                </div>
            </div>
    </div>
</div>
    <script>
        $(document).ready(function() {
            $.ajax({
                url: '/api/sector',
                method: 'GET',
                success: function(response) {
                    createMixedChart(response);
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error: ', status, error);
                }
            });

            function createMixedChart(data) {
                const ctx = document.getElementById('mixedChart').getContext('2d');

                // Filter out null sectors if needed
                const filteredData = data.filter(item => item.sector !== null);

                const labels = filteredData.map(item => item.sector);
                const dataSectorCount = filteredData.map(item => item.sector_count);
                const dataIntensity = filteredData.map(item => parseFloat(item.avg_intensity));
                const dataLikelihood = filteredData.map(item => parseFloat(item.avg_likelihood));
                const dataRelevance = filteredData.map(item => parseFloat(item.avg_relevance));

                new Chart(ctx, {
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                type: 'bar',
                                label: 'Sector Count',
                                data: dataSectorCount,
                                backgroundColor: 'rgba(54, 162, 235, 0.7)', // Bright color
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 1,
                                yAxisID: 'y',
                            },
                            {
                                type: 'line',
                                label: 'Intensity',
                                data: dataIntensity,
                                backgroundColor: 'rgba(75, 192, 192, 0.7)', // Bright color
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 2,
                                fill: false,
                                yAxisID: 'y1',
                            },
                            {
                                type: 'line',
                                label: 'Likelihood',
                                data: dataLikelihood,
                                backgroundColor: 'rgba(255, 206, 86, 0.7)', // Bright color
                                borderColor: 'rgba(255, 206, 86, 1)',
                                borderWidth: 2,
                                fill: false,
                                yAxisID: 'y1',
                            },
                            {
                                type: 'line',
                                label: 'Relevance',
                                data: dataRelevance,
                                backgroundColor: 'rgba(255, 99, 132, 0.7)',
                                borderColor: 'rgba(255, 99, 132, 1)',
                                borderWidth: 2,
                                fill: false,
                                yAxisID: 'y1',
                            }
                        ]
                    },
                    options: {
                        scales: {
                            x: {
                                display: false, // Hide x-axis labels
                            },
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                title: {
                                    display: true,
                                    text: 'Sector Count'
                                },
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                title: {
                                    display: true,
                                    text: 'Average Values'
                                },
                                grid: {
                                    drawOnChartArea: false, // Only want the grid lines for one axis to show up
                                },
                            },
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return `${context.dataset.label}: ${context.raw}`;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });

        document.addEventListener("DOMContentLoaded", function() {
            // Fetch data from the API
            fetch('/api/swot-pestle')
                .then(response => response.json())
                .then(data => {
                    // Prepare the data for the radar chart
                    const labels = data.map(item => item.pestle);
                    const strengthData = data.map(item => item.Strength);
                    const weaknessData = data.map(item => item.Weakness);
                    const opportunityData = data.map(item => item.Opportunity);
                    const threatData = data.map(item => item.Threat);

                    // Configure the radar chart
                    var options = {
                        series: [
                            {
                                name: 'Strength',
                                data: strengthData
                            },
                            {
                                name: 'Weakness',
                                data: weaknessData
                            },
                            {
                                name: 'Opportunity',
                                data: opportunityData
                            },
                            {
                                name: 'Threat',
                                data: threatData
                            }
                        ],
                        chart: {
                            height: 450,
                            type: 'radar'
                        },
                        xaxis: {
                            categories: labels
                        },
                        plotOptions: {
                            radar: {
                                polygons: {
                                    strokeColors: '#e9e9e9',
                                    strokeWidth: 1,
                                    fill: {
                                        colors: ['#f8f8f8']
                                    }
                                }
                            }
                        },
                        stroke: {
                            width: 2
                        },
                        markers: {
                            size: 4
                        },
                        legend: {
                            position: 'top',
                            horizontalAlign: 'left'
                        }
                    };

                    // Render the radar chart
                    var chart = new ApexCharts(document.querySelector("#chart"), options);
                    chart.render();
                });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('.label-button').on('click', function() {
                // Remove 'active' class from all buttons
                $('.label-button').removeClass('active');

                // Add 'active' class to the clicked button
                $(this).addClass('active');

                // Get the data-include value
                var includeFile = $(this).data('include');

                // Update the map content based on the clicked label
                $.ajax({
                    url: '/' + includeFile,
                    method: 'GET',
                    success: function(response) {
                        $('.map').html(response);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading content: ', status, error);
                    }
                });
            });
        });
    </script>
     <script>
        document.addEventListener('DOMContentLoaded', () => {
            fetch('/api/pestle-data')
                .then(response => response.json())
                .then(data => {
                    buildTreemap(data);
                });
        });

        function buildTreemap(data) {
            const treemapData = data.map(item => ({
                name: item.pestle,
                value: item.sector_count,
                totalTopicCount: item.total_topic_count_with_pestle,
                totalSourceCount: item.total_source_count_with_pestle
            }));

            const width = document.getElementById('treemap-container').clientWidth;
            const height = document.getElementById('treemap-container').clientHeight;
            const svg = d3.select("#treemap-container").append("svg")
                .attr("width", width)
                .attr("height", height);

            const root = d3.hierarchy({ children: treemapData })
                .sum(d => d.value);

            d3.treemap().size([width, height]).padding(2)(root);

            const cells = svg.selectAll("g")
                .data(root.leaves())
                .enter().append("g")
                .attr("transform", d => `translate(${d.x0},${d.y0})`)
                .on("mouseover", function(event, d) {
                    d3.select(this).select("rect").style("fill", "#ff6347");
                    d3.select(".tooltip")
                        .style("opacity", 1)
                        .html(`PESTLE: ${d.data.name}<br>Sectors: ${d.data.value}<br>Total Topics: ${d.data.totalTopicCount}<br>Total Sources: ${d.data.totalSourceCount}`)
                        .style("left", `${event.pageX + 5}px`)
                        .style("top", `${event.pageY - 28}px`);
                })
                .on("mouseout", function() {
                    d3.select(this).select("rect").style("fill", "#69b3a2");
                    d3.select(".tooltip").style("opacity", 0);
                });

            cells.append("rect")
                .attr("id", d => d.data.name)
                .attr("width", d => d.x1 - d.x0)
                .attr("height", d => d.y1 - d.y0)
                .style("fill", "#69b3a2");

            cells.append("text")
                .attr("x", 4)
                .attr("y", 20)
                .text(d => d.data.name)
                .style("font-size", "12px");

            d3.select("body").append("div")
                .attr("class", "tooltip");
        }
    </script>
     <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const apiUrl = '/api/year-data';
            let chart;
            let data = [];

            // Fetch data and initialize chart
            fetch(apiUrl)
                .then(response => response.json())
                .then(responseData => {
                    data = responseData;

                    // Populate year selection
                    populateYearSelect(data);

                    // Initial chart render with the first year
                    if (data.length > 0) {
                        const initialYear = data[0].end_year;
                        renderChart(initialYear);
                    }
                })
                .catch(error => console.error('Error fetching the data:', error));

            function populateYearSelect(data) {
                const yearSelect = document.getElementById('yearSelect');
                const years = [...new Set(data.map(item => item.end_year))].sort();
                years.forEach(year => {
                    const option = document.createElement('option');
                    option.value = year;
                    option.text = year;
                    yearSelect.add(option);
                });

                yearSelect.addEventListener('change', (event) => {
                    const selectedYear = event.target.value;
                    renderChart(selectedYear);
                });
            }

            function renderChart(year) {
                const filteredData = data.filter(item => item.end_year == year);

                // Prepare data for the chart
                const labels = [...new Set(filteredData.map(item => item.pestle))];
                const datasets = labels.map(pestle => {
                    const pestleData = filteredData.filter(item => item.pestle === pestle);
                    return {
                        label: pestle,
                        data: pestleData.map(item => ({ x: item.pestle, y: parseFloat(item.avg_intensity) })),
                        borderColor: getRandomColor(),
                        fill: false
                    };
                });

                if (chart) {
                    chart.destroy();
                }

                // Create the line chart
                const ctx = document.getElementById('lineChart').getContext('2d');
                chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: datasets
                    },
                    options: {
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Pestle'
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Average Intensity'
                                }
                            }
                        }
                    }
                });
            }

            // Function to generate random color for each dataset
            function getRandomColor() {
                const letters = '0123456789ABCDEF';
                let color = '#';
                for (let i = 0; i < 6; i++) {
                    color += letters[Math.floor(Math.random() * 16)];
                }
                return color;
            }
        });
    </script>
     <script>
        const pestleSelect = document.getElementById('pestle-select');
        const chartCanvas = document.getElementById('pestleChart').getContext('2d');
        let pestleData = [];
        let pestleChart;

        // Function to fetch data from the API
        async function fetchData() {
            try {
                const response = await fetch('/api/pestle-data');
                pestleData = await response.json();

                // Populate the pestle options
                pestleData.forEach((data, index) => {
                    const option = document.createElement('option');
                    option.value = index;
                    option.textContent = data.pestle;
                    pestleSelect.appendChild(option);
                });

                // Initialize chart with the first pestle
                updateChart();
            } catch (error) {
                console.error('Error fetching data:', error);
            }
        }

        function updateChart() {
            const selectedIndex = pestleSelect.value;
            const selectedPestle = pestleData[selectedIndex];

            // Extract sectors and counts
            const sectors = selectedPestle.sectors_with_counts.split(', ').map(s => s.split(': ')[0]);
            const sectorCounts = selectedPestle.sectors_with_counts.split(', ').map(s => parseInt(s.split(': ')[1]));
            const topicCounts = selectedPestle.sector_topic_counts.split(', ').map(s => parseInt(s.split(': ')[1]));
            const sourceCounts = selectedPestle.sector_source_counts.split(', ').map(s => parseInt(s.split(': ')[1]));

            // Update or create the chart
            if (pestleChart) {
                pestleChart.destroy();
            }

            pestleChart = new Chart(chartCanvas, {
                type: 'line',
                data: {
                    labels: sectors,
                    datasets: [
                        {
                            label: 'Sectors',
                            data: sectorCounts,
                            borderColor: 'rgba(255, 99, 132, 1)',
                            fill: false,
                            tension: 0.3
                        },
                        {
                            label: 'Topics',
                            data: topicCounts,
                            borderColor: 'rgba(54, 162, 235, 1)',
                            fill: false,
                            tension: 0.3
                        },
                        {
                            label: 'Sources',
                            data: sourceCounts,
                            borderColor: 'rgba(75, 192, 192, 1)',
                            fill: false,
                            tension: 0.3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Fetch data on page load
        fetchData();
    </script>
</body>
</html>
