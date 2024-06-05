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

// Retrieve task analytics
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

$page_name = "Analytics";
include("include/sidebar.php");

?>

<div class="mains">
    <div class="container">
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
    </div>
</div>

<script>
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