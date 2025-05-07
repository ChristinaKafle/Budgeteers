fetch('chart-data.php')
    .then(response => response.json())
    .then(data => {
        const renderPie = (ctx, pieData) => {
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: Object.keys(pieData),
                    datasets: [{
                        label: 'Income vs Expense',
                        data: Object.values(pieData),
                        backgroundColor: ['green', 'red'],
                    }]
                }
            });
        };

        const renderBar = (ctx, barData, labelText) => {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: barData.map(item => item.category),
                    datasets: [{
                        label: labelText,
                        data: barData.map(item => item.total),
                        backgroundColor: 'blue'
                    }]
                }
            });
        };
        const renderHistogram = (ctx, histData, labelText) => {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: histData.map(item => item.day),
                    datasets: [{
                        label: labelText,
                        data: histData.map(item => item.total),
                        backgroundColor: 'orange',
                        borderColor: 'black',       // Border color for bars
                        borderWidth: 1,             // Thickness of the border
                        categoryPercentage: 1.0,
                        barPercentage: 1.0,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Amount'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        }
                    }
                }
            });
        };
        
        
        // Render Yearly
        renderPie(document.getElementById('yearlyPieChart'), data.yearly.pie);
        renderBar(document.getElementById('yearlyBarChart'), data.yearly.bar, "Yearly Expenses by Category");
        renderHistogram(document.getElementById('yearlyHistChart'), data.yearly.histogram, "Yearly Daily Expenses");

        // Render Monthly
        renderPie(document.getElementById('monthlyPieChart'), data.monthly.pie);
        renderBar(document.getElementById('monthlyBarChart'), data.monthly.bar, "Monthly Expenses by Category");
        renderHistogram(document.getElementById('monthlyHistChart'), data.monthly.histogram, "Monthly Daily Expenses");

        // Render Daily
        renderPie(document.getElementById('dailyPieChart'), data.daily.pie);
        renderBar(document.getElementById('dailyBarChart'), data.daily.bar, "Daily Expenses by Category");
        renderHistogram(document.getElementById('dailyHistChart'), data.daily.histogram, "Daily Expenses");
    });
