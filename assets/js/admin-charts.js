jQuery(document).ready(function($) {
    // Performance Chart
    new Chart(document.getElementById('performanceChart'), {
        type: 'bar',
        data: {
            labels: performanceData.map(item => `Phòng ${item.department_id}`),
            datasets: [{
                label: 'Điểm TB',
                data: performanceData.map(item => item.avg_score),
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }, {
                label: 'Số Task',
                data: performanceData.map(item => item.total_tasks),
                backgroundColor: 'rgba(255, 99, 132, 0.5)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: {  
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Tenure Chart
    new Chart(document.getElementById('tenureChart'), {
        type: 'pie',
        data: {
            labels: tenureData.map(item => `${item.years} năm`),
            datasets: [{
                data: tenureData.map(item => item.count),
                label: 'Số nhân viên',
                backgroundColor: [
                    'rgba(255, 99, 132, 0.5)',
                    'rgba(54, 162, 235, 0.5)',
                    'rgba(255, 206, 86, 0.5)',
                    'rgba(75, 192, 192, 0.5)',
                    'rgba(153, 102, 255, 0.5)'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });

    // Department Distribution Chart
    new Chart(document.getElementById('departmentChart'), {
        type: 'doughnut',
        data: {
            labels: departmentData.map(item => `${item.department_name}`),
            datasets: [{
                data: departmentData.map(item => item.employee_count),
                label: 'Số nhân viên',
                backgroundColor: [
                    'rgba(255, 99, 132, 0.5)',
                    'rgba(54, 162, 235, 0.5)',
                    'rgba(255, 206, 86, 0.5)',
                    'rgba(75, 192, 192, 0.5)',
                    'rgba(153, 102, 255, 0.5)'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });

    // Salary Costs Chart
    new Chart(document.getElementById('salaryChart'), {
        type: 'bar',
        data: {
            labels: salaryData.map(item => `${item.department_name}`),
            datasets: [{
                label: 'Tổng Chi Phí',
                data: salaryData.map(item => item.total_cost),
                backgroundColor: 'rgba(75, 192, 192, 0.5)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }, {
                label: 'Lương TB',
                data: salaryData.map(item => item.avg_salary),
                backgroundColor: 'rgba(153, 102, 255, 0.5)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('vi-VN', {
                                style: 'currency',
                                currency: 'VND'
                            }).format(value);
                        }
                    }
                }
            }
        }
    });
}); 