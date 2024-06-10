<?php

require 'authentication.php'; // admin authentication check 

// auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
  header('Location: index.php');
}

// check admin
$user_role = $_SESSION['user_role'];

if (isset($_GET['delete_task'])) {
  $action_id = $_GET['task_id'];
  $sql = "DELETE FROM task_info WHERE task_id = :id";
  $sent_po = "task-info.php";
  $obj_admin->delete_data_by_this_method($sql, $action_id, $sent_po);
}

if (isset($_POST['add_task_post'])) {
  $task_title = $_POST['task_title'];
  $task_description = $_POST['task_description'];
  $start_time = $_POST['t_start_time'];
  $end_time = $_POST['t_end_time'];
  $assigned_to = $_POST['assign_to']; // This will be an array of user IDs

  // Iterate through each selected user and insert a task for each user
  foreach ($assigned_to as $user_id) {
    $sql = "INSERT INTO task_info (t_title, t_description, t_start_time, t_end_time, t_user_id) VALUES (:task_title, :task_description, :start_time, :end_time, :user_id)";
    $stmt = $obj_admin->db->prepare($sql);
    $stmt->bindParam(':task_title', $task_title);
    $stmt->bindParam(':task_description', $task_description);
    $stmt->bindParam(':start_time', $start_time);
    $stmt->bindParam(':end_time', $end_time);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
  }

  // Redirect or display a success message
  header('Location: task-info.php');
}

$page_name = "Task_Info";
include("include/sidebar.php");

?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
  $(document).ready(function() {
    $('#aassign_to').select2({
      placeholder: "Select Employee...",
      allowClear: true,
      width: "100%"
    });
  });
</script>
<style>
    body {
    /* background-image: url(assets/pictures/picture1.png); */
    /* background-image: linear-gradient(red, yellow); */
    background-size: cover;
    background-position: center;
    background-size: auto;
    background-repeat: no-repeat;
  }

  .select2-container--default .select2-selection--multiple {
    border: 1px solid #ccc;
    border-radius: 4px;
    padding: 6px;
    height: auto;
  }

  .select2-container--default .select2-selection--multiple .select2-selection__choice {
    background-color: #5bc0de;
    border: 1px solid #46b8da;
    padding: 3px 10px;
    color: #fff;
    border-radius: 4px;
  }

  .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: #fff;
    margin-right: 8px;
  }

  .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
    color: #fff;
    background-color: #d9534f;
  }

  .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    background-color: transparent;
    border: none;
    /* border-right: 1px solid #aaa; */
    border-top-left-radius: 4px;
    border-bottom-left-radius: 4px;
    color: #fff;
    cursor: pointer;
    font-size: 1em;
    font-weight: bold;
    padding: 0 4px;

    position: absolute;
    left: 0;
    top: 0;
  }

  .select2-container--default .select2-selection--multiple .select2-selection__choice__display {
    cursor: default;
    padding-left: 20px;
    padding-right: 5px;
  }

  .select2-container .select2-search--inline .select2-search__field {
    height: 21px;
  }
</style>
<!-- Modal -->
<div class="modal fade" id="myModal" role="dialog">
  <div class="modal-dialog add-category-modal">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h2 class="modal-title text-center">Assign New Task</h2> 
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <form role="form" action="" method="post" autocomplete="off">
              <div class="form-horizontal">
                <div class="form-group">
                  <label class="control-label col-sm-5">Task Title</label>
                  <div class="col-sm-7">
                    <input type="text" placeholder="Task Title" id="task_title" name="task_title" list="expense" class="form-control" id="default" required>
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-sm-5">Task Description</label>
                  <div class="col-sm-7">
                    <textarea name="task_description" id="task_description" placeholder="Text Deskcription" class="form-control" rows="5" cols="5"></textarea>
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-sm-5">Start Time</label>
                  <div class="col-sm-7">
                    <input type="text" name="t_start_time" id="t_start_time" class="form-control">
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-sm-5">End Time</label>
                  <div class="col-sm-7">
                    <input type="text" name="t_end_time" id="t_end_time" class="form-control">
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-sm-5">Assign To</label>
                  <div class="col-sm-7">
                    <?php
                    $sql = "SELECT user_id, fullname FROM tbl_admin WHERE user_role = 2";
                    $info = $obj_admin->manage_all_info($sql);
                    ?>
                    <select class="form-control" name="assign_to[]" id="aassign_to" aria-multiselectable="true" multiple required>
                      <option value="">Select Employee...</option>
                      <?php while ($row = $info->fetch(PDO::FETCH_ASSOC)) { ?>
                        <option value="<?php echo $row['user_id']; ?>"><?php echo $row['fullname']; ?></option>
                      <?php } ?>
                    </select>

                  </div>

                </div>
                <div class="form-group">
                </div>
                <div class="form-group">
                  <div class="col-sm-offset-3 col-sm-3">
                    <button type="submit" name="add_task_post" class="btn btn-success-custom">Assign Task</button>
                  </div>
                  <div class="col-sm-3">
                    <button type="submit" class="btn btn-danger-custom" data-dismiss="modal">Cancel</button>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>




<?php if ($user_role == 1) { ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="well well-custom">
                <div class="gap"></div>
                <div class="row">
                    <div class="col-md-8">
                        <div class="btn-group">
                            
                                <div class="btn-group">
                                    <button class="btn btn-warning btn-menu" data-toggle="modal" data-target="#myModal" style="color: black" >Assign New Task</button>
                                </div>
                            
                        </div>
                    </div>
                </div>
                <center>
                    <h3>Task Management Section</h3>
                </center>
                <div class="gap"></div>
                <div class="gap"></div>
                <div class="table-responsive">
                    <table class="table table-codensed table-custom">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Employee Name</th>
                                <th>View Task</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch employee names from tbl_admin
                            $sql = "SELECT user_id, fullname FROM tbl_admin WHERE user_role = 2 ORDER BY user_id DESC";
                            $info = $obj_admin->manage_all_info($sql);
                            $serial = 1;
                            $num_row = $info->rowCount();
                            if ($num_row == 0) {
                                echo '<tr><td colspan="3">No Data found</td></tr>';
                            } else {
                                while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                                    ?>
                                    <tr>
                                        <td><?php echo $serial++; ?></td>
                                        <td><?php echo $row['fullname']; ?></td>
                                        <td>
                                            <a title="View" href="task-details.php?user_id=<?php echo $row['user_id']; ?>">
                                                <span class="glyphicon glyphicon-folder-open"></span>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php } else { // Non-admin user ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="well well-custom">
                    <div class="gap"></div>
                    <center>
                        <h3 >Your Assigned Tasks</h3>
                    </center>
                    <div class="gap"></div>
                    <div class="table-responsive">
                        <table class="table table-condensed table-custom">
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
                                // Fetch tasks for the logged-in user
                                $sql = "SELECT * FROM task_info WHERE t_user_id = :user_id ORDER BY task_id DESC";
                                $stmt = $obj_admin->db->prepare($sql);
                                $stmt->bindParam(':user_id', $user_id);
                                $stmt->execute();
                                $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                $serial = 1;
                                if (empty($tasks)) {
                                    echo '<tr><td colspan="6">No Tasks found</td></tr>';
                                } else {
                                    foreach ($tasks as $task) {
                                        ?>
                                        <tr>
                                            <td><?php echo $serial++; ?></td>
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
                 
                                            </td>
              
                                        </tr>
                                        <?php
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>



<?php

include("include/footer.php");



?>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script type="text/javascript">
  flatpickr('#t_start_time', {
    enableTime: true
  });

  flatpickr('#t_end_time', {
    enableTime: true
  });
</script>