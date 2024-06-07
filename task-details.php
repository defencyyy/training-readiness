<?php

require 'authentication.php'; // admin authentication check 

// auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
    header('Location: index.php');
    exit;
}

// check admin
$user_role = $_SESSION['user_role'];

// Get the user_id from the URL
$employee_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;

// if (!$employee_id) {
//     echo "<p>Invalid Employee ID.</p>";
//     exit;
// }

if (isset($_GET['delete_task'])) {
	$action_id = $_GET['task_id'];
  
	$sql = "DELETE FROM task_info WHERE task_id = :id";
	$sent_po = "task-info.php";
	$obj_admin->delete_data_by_this_method($sql, $action_id, $sent_po);
  }

// Fetch employee name
$sql = "SELECT fullname FROM tbl_admin WHERE user_id = :employee_id";
$stmt = $obj_admin->db->prepare($sql);
$stmt->execute(['employee_id' => $employee_id]);
$employee_name = $stmt->fetchColumn();

// if (!$employee_name) {
//     echo "<p>Employee not found.</p>";
//     exit;
// }

// Check if the user has tasks
$sql = "SELECT * FROM task_info WHERE t_user_id = :employee_id";
$stmt = $obj_admin->db->prepare($sql);
$stmt->execute(['employee_id' => $employee_id]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_name = "Employee Tasks";
include("include/sidebar.php");
?>

<div class="row">
    <div class="col-md-12">
        <div class="well well-custom">
        	<?php if (!empty($tasks)): ?>
                <h3 class="text-center bg-primary" style="padding: 7px;">Tasks for <?php echo htmlspecialchars($employee_name); ?></h3><br>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Task Title</th>
                                <th>Description</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Status</th>
								<th>Actions</th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $serial = 1;
                            foreach ($tasks as $task) {
                            ?>
                                <tr>
                                    <td><?php echo $serial; $serial++; ?></td>
                                    <td><?php echo htmlspecialchars($task['t_title']); ?></td>
                                    <td><?php echo htmlspecialchars($task['t_description']); ?></td>
                                    <td><?php echo htmlspecialchars($task['t_start_time']); ?></td>
                                    <td><?php echo htmlspecialchars($task['t_end_time']); ?></td>
                                    <td><?php
                                        if ($task['status'] == 1) {
                                            echo "In Progress";
                                        } elseif ($task['status'] == 2) {
                                            echo "Completed";
                                        } else {
                                            echo "Incomplete";
                                        }
                                        ?></td>
										<td><a title="Update Task" href="edit-task.php?task_id=<?php echo $task['task_id']; ?>"><span class="glyphicon glyphicon-edit"></span></a>&nbsp;&nbsp;
                  <?php if ($user_role == 1) { ?>
                    <a title="Delete" href="?delete_task=delete_task&task_id=<?php echo $task['task_id']; ?>" onclick=" return check_delete();"><span class="glyphicon glyphicon-trash"></span></a>
                </td>
              <?php } ?>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <h3 class="text-center bg-primary" style="padding: 7px;">No Tasks for <?php echo htmlspecialchars($employee_name); ?></h3><br>
                <p class="text-center">There are no tasks assigned to this employee.</p>
            <?php endif; ?>
            <div class="form-group">
                <div class="col-sm-3">
                    <a title="Back to Employee List" href="task-info.php"><span class="btn btn-success-custom btn-xs">Go Back</span></a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("include/footer.php"); ?>
