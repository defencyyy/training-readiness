<?php
require 'authentication.php'; // admin authentication check

// Auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
    header('Location: index.php');
    exit();
}

$user_role = $_SESSION['user_role'];
if ($user_role != 1 && $user_role != 2) {
    header('Location: task-info.php');
    exit();
}

// Get user ID from the session or a request parameter
$employee_id = $_GET['employee_id'] ?? $user_id;

// Retrieve employee's name from tbl_admin
$stmt = $obj_admin->db->prepare("SELECT fullname FROM tbl_admin WHERE user_id = :employee_id");
$stmt->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
$stmt->execute();
$employee_data = $stmt->fetch(PDO::FETCH_ASSOC);
$employee_name = $employee_data['fullname'];

// Retrieve task analytics for the specific user
$sql = "SELECT 
            COUNT(*) as total_tasks, 
            SUM(status = 0) as total_incomplete, 
            SUM(status = 1) as total_in_progress, 
            SUM(status = 2) as total_complete 
        FROM task_info 
        WHERE t_user_id = :employee_id";
$stmt = $obj_admin->db->prepare($sql);
$stmt->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
$stmt->execute();
$task_data = $stmt->fetch(PDO::FETCH_ASSOC);

$total_tasks = $task_data['total_tasks'];
$total_incomplete = $task_data['total_incomplete'];
$total_in_progress = $task_data['total_in_progress'];
$total_complete = $task_data['total_complete'];

$percent_complete = ($total_tasks > 0) ? ($total_complete / $total_tasks) * 100 : 0;
$percent_in_progress = ($total_tasks > 0) ? ($total_in_progress / $total_tasks) * 100 : 0;
$percent_incomplete = ($total_tasks > 0) ? ($total_incomplete / $total_tasks) * 100 : 0;

// If the user is an admin, retrieve overall task analytics
if ($user_role == 1) {
    $sql = "SELECT 
                COUNT(*) as overall_total_tasks, 
                SUM(status = 0) as overall_total_incomplete, 
                SUM(status = 1) as overall_total_in_progress, 
                SUM(status = 2) as overall_total_complete 
            FROM task_info";
    $stmt = $obj_admin->db->prepare($sql);
    $stmt->execute();
    $overall_task_data = $stmt->fetch(PDO::FETCH_ASSOC);

    $overall_total_tasks = $overall_task_data['overall_total_tasks'];
    $overall_total_incomplete = $overall_task_data['overall_total_incomplete'];
    $overall_total_in_progress = $overall_task_data['overall_total_in_progress'];
    $overall_total_complete = $overall_task_data['overall_total_complete'];

    $overall_percent_complete = ($overall_total_tasks > 0) ? ($overall_total_complete / $overall_total_tasks) * 100 : 0;
    $overall_percent_in_progress = ($overall_total_tasks > 0) ? ($overall_total_in_progress / $overall_total_tasks) * 100 : 0;
    $overall_percent_incomplete = ($overall_total_tasks > 0) ? ($overall_total_incomplete / $overall_total_tasks) * 100 : 0;
}
$stmt = $obj_admin->db->prepare("SELECT t_title, status, COUNT(*) as count FROM task_info GROUP BY t_title, status");
$stmt->execute();
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
$page_name = "Analytics";
include("include/sidebar.php");

?>

<div class="mains">
    <div class="container">
        <?php if ($user_role == 1) : ?>
            <h2>Overall Task Analytics</h2>
            <div class="analytics">
                <p>Total Tasks: <?php echo $overall_total_tasks; ?></p>
                <div class="chart-container">
                    <canvas id="overallCompleteChart" width="150" height="150"></canvas>
                    <canvas id="overallInProgressChart" width="150" height="150"></canvas>
                    <canvas id="overallIncompleteChart" width="150" height="150"></canvas>
                </div>
            </div>
            <h2>Specific Task Analytics</h2>
            <div class="analytics">
                <?php
                $task_stats = [];

                // Process tasks and calculate statistics
                foreach ($tasks as $task) {
                    $title = $task['t_title'];
                    $status = $task['status'];
                    $count = $task['count'];

                    // Group tasks by title
                    if (!isset($task_stats[$title])) {
                        $task_stats[$title] = [
                            'complete' => 0,
                            'in_progress' => 0,
                            'incomplete' => 0
                        ];
                    }
                    switch ($status) {
                        case 0:
                            $task_stats[$title]['incomplete'] += $count;
                            break;
                        case 1:
                            $task_stats[$title]['in_progress'] += $count;
                            break;
                        case 2:
                            $task_stats[$title]['complete'] += $count;
                            break;
                    }
                }
                foreach ($task_stats as $title => $stats) {
                    $total = $stats['complete'] + $stats['in_progress'] + $stats['incomplete'];
                    echo "<div class='task-container'>";
                    echo "<h3>Task: $title</h3>";
                    echo "<div class='chart-container'>";
                    echo "<canvas class='completeChart' data-value='" . (($stats['complete'] / $total) * 100) . "'data-total='" . $total . "'></canvas>";
                    echo "<canvas class='inProgressChart' data-value='" . (($stats['in_progress'] / $total) * 100) . "'></canvas>";
                    echo "<canvas class='incompleteChart' data-value='" . (($stats['incomplete'] / $total) * 100) . "'></canvas>";
                    echo "</div>";
                    echo "</div>";
                }

                ?>
            </div>

        <?php endif; ?>
        <?php if ($user_role != 1) : ?>
            <h2>Employee Task Analytics</h2>
            <div class="analytics">
                <h3>Employee Name: <?php echo $employee_name; ?></h3>
                <p>Total Tasks: <?php echo $total_tasks; ?></p>
                <div class="chart-container">
                    <canvas id="completeChart" width="150" height="150"></canvas>
                    <canvas id="inProgressChart" width="150" height="150"></canvas>
                    <canvas id="incompleteChart" width="150" height="150"></canvas>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    <?php if ($user_role == 1) : ?>
        var ctxOverallComplete = document.getElementById('overallCompleteChart').getContext('2d');
        var ctxOverallInProgress = document.getElementById('overallInProgressChart').getContext('2d');
        var ctxOverallIncomplete = document.getElementById('overallIncompleteChart').getContext('2d');

        var overallCompleteChart = new Chart(ctxOverallComplete, {
            type: 'doughnut',
            data: {
                labels: ['Complete', 'Total Tasks'],
                datasets: [{
                    data: [<?php echo $overall_percent_complete; ?>, 100 - <?php echo $overall_percent_complete; ?>],
                    backgroundColor: ['#00B16A', '#dad9d9']
                }]
            },
            options: {
                title: {
                    display: true,
                    text: 'Tasks Complete'
                },
                responsive: false,
                maintainAspectRatio: false
            }
        });

        var overallInProgressChart = new Chart(ctxOverallInProgress, {
            type: 'doughnut',
            data: {
                labels: ['In Progress', 'Total Tasks'],
                datasets: [{
                    data: [<?php echo $overall_percent_in_progress; ?>, 100 - <?php echo $overall_percent_in_progress; ?>],
                    backgroundColor: ['#FFC107', '#dad9d9']
                }]
            },
            options: {
                title: {
                    display: true,
                    text: 'Tasks In Progress'
                },
                responsive: false,
                maintainAspectRatio: false
            }
        });

        var overallIncompleteChart = new Chart(ctxOverallIncomplete, {
            type: 'doughnut',
            data: {
                labels: ['Incomplete', 'Total Tasks'],
                datasets: [{
                    data: [<?php echo $overall_percent_incomplete; ?>, 100 - <?php echo $overall_percent_incomplete; ?>],
                    backgroundColor: ['#FF5733', '#dad9d9']
                }]
            },
            options: {
                title: {
                    display: true,
                    text: 'Tasks Incomplete'
                },
                responsive: false,
                maintainAspectRatio: false
            }
        });
        document.addEventListener("DOMContentLoaded", function() {
            var completeCharts = document.querySelectorAll('.completeChart');
            var inProgressCharts = document.querySelectorAll('.inProgressChart');
            var incompleteCharts = document.querySelectorAll('.incompleteChart');

            completeCharts.forEach(function(chart) {
                var value = parseInt(chart.getAttribute('data-value'));
                var total = parseInt(chart.getAttribute('data-total'));
                var completeCount = parseInt(chart.getAttribute('data-complete-count'));
                var inProgressCount = parseInt(chart.getAttribute('data-in-progress-count'));
                var incompleteCount = parseInt(chart.getAttribute('data-incomplete-count'));
                createDonutChart(chart, value, 'Complete', '#00B16A', total, completeCount, inProgressCount, incompleteCount);
            });

            inProgressCharts.forEach(function(chart) {
                var value = parseInt(chart.getAttribute('data-value'));
                var total = parseInt(chart.getAttribute('data-total'));
                var completeCount = parseInt(chart.getAttribute('data-complete-count'));
                var inProgressCount = parseInt(chart.getAttribute('data-in-progress-count'));
                var incompleteCount = parseInt(chart.getAttribute('data-incomplete-count'));
                createDonutChart(chart, value, 'In Progress', '#FFC107', total, completeCount, inProgressCount, incompleteCount);
            });

            incompleteCharts.forEach(function(chart) {
                var value = parseInt(chart.getAttribute('data-value'));
                var total = parseInt(chart.getAttribute('data-total'));
                var completeCount = parseInt(chart.getAttribute('data-complete-count'));
                var inProgressCount = parseInt(chart.getAttribute('data-in-progress-count'));
                var incompleteCount = parseInt(chart.getAttribute('data-incomplete-count'));
                createDonutChart(chart, value, 'Incomplete', '#FF5733', total, completeCount, inProgressCount, incompleteCount);
            });

            function createDonutChart(canvas, value, label, color, total, completeCount, inProgressCount, incompleteCount) {
                var ctx = canvas.getContext('2d');
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: [label + ': ' + value + '%', 'Total: ' + total],
                        datasets: [{
                            data: [value, 100 - value],
                            backgroundColor: [color, '#dad9d9']
                        }]
                    },
                    options: {
                        title: {
                            display: true,
                            text: label + ' Tasks (Complete: ' + completeCount + ', In Progress: ' + inProgressCount + ', Incomplete: ' + incompleteCount + ')'
                        },
                        responsive: false,
                        maintainAspectRatio: false
                    }
                });
            }
        });

    <?php endif; ?>
    var ctxComplete = document.getElementById('completeChart').getContext('2d');
    var ctxInProgress = document.getElementById('inProgressChart').getContext('2d');
    var ctxIncomplete = document.getElementById('incompleteChart').getContext('2d');

    var completeChart = new Chart(ctxComplete, {
        type: 'doughnut',
        data: {
            labels: ['Complete', 'Total Tasks'],
            datasets: [{
                data: [<?php echo $percent_complete; ?>, 100 - <?php echo $percent_complete; ?>],
                backgroundColor: ['#00B16A', '#dad9d9']
            }]
        },
        options: {
            title: {
                display: true,
                text: 'Tasks Complete'
            },
            responsive: false,
            maintainAspectRatio: false
        }
    });

    var inProgressChart = new Chart(ctxInProgress, {
        type: 'doughnut',
        data: {
            labels: ['In Progress', 'Total Tasks'],
            datasets: [{
                data: [<?php echo $percent_in_progress; ?>, 100 - <?php echo $percent_in_progress; ?>],
                backgroundColor: ['#FFC107', '#dad9d9']
            }]
        },
        options: {
            title: {
                display: true,
                text: 'Tasks In Progress'
            },
            responsive: false,
            maintainAspectRatio: false
        }
    });

    var incompleteChart = new Chart(ctxIncomplete, {
        type: 'doughnut',
        data: {
            labels: ['Incomplete', 'Total Tasks'],
            datasets: [{
                data: [<?php echo $percent_incomplete; ?>, 100 - <?php echo $percent_incomplete; ?>],
                backgroundColor: ['#FF5733', '#dad9d9']
            }]
        },
        options: {
            title: {
                display: true,
                text: 'Tasks Incomplete'
            },
            responsive: false,
            maintainAspectRatio: false
        }
    });
</script>

<?php
include("include/footer.php");
?>