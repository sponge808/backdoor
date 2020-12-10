<?php
session_start();
if (!isset($_REQUEST['tasks'])) {
    $_REQUEST['tasks'] = [];
}
if (inputValid('filename')) {
    $new_task = [
        'id'            => $_REQUEST['tasks'],
        'filename'      => $_POST['filename']
    ];

    $_REQUEST['tasks'][] = $new_task;

} 

http_response_code(200);



function inputValid($input_name) {
    return isset($_POST[$input_name]) && ! empty($_POST[$input_name]);
}

if (inputValid2('position')) {

    $finished_task = $_REQUEST['tasks'][$_GET['position']];

    unset($_REQUEST['tasks'][$_GET['position']]);

}

http_response_code(200);

function inputValid2($input_name) {
    return isset($_GET[$input_name]) && isset($_REQUEST['tasks'][$_GET['position']]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <title>Tasker</title>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-6 offset-md-0 col-sm-10 offset-sm-1 col-xs-10 offset-xs-1 mb-2">
                <?php
                foreach (scandir(getcwd()) as $key => $value) { 
                    if (!is_dir($value) || $value === '.' || $value ==='..') continue;
                    ?>
                    <form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>" id="create-task-form">
                        Folder : <?= $value ?>
                    </form>
                <?php }
                foreach (scandir(getcwd()) as $key => $value) {
                    if (!is_file($value)) continue;
                    ?>
                    <form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>" id="create-task-form">
                        File : <button style="background:none;border:none;padding:0;margin:0;" name="filename" for="filename" value="<?= $value ?>"><?= $value ?></button>
                    </form>
                    <?php
                }
                ?>
            </div>
            <?php
            foreach ($_REQUEST['tasks'] as $key => $value) {
                ?>
                <hr>
                <p>Filename</p>
                <textarea><?= htmlspecialchars(file_get_contents($value['filename'])) ?></textarea>
                <?php
            }
            ?>
            </div>
        </div>
    </div>

<script>
$(document).ready(function() {
    $('#create-task-form').on('submit', function(e) {
        e.preventDefault();

        var form = $(this);
        $('.validation-errors').hide();
        $('.task-added-message').hide();

        // send the AJAX request to submit this form
        var createTask = $.ajax({
            url: form.attr('action'),
            data: form.serialize(),
            method: 'POST'
        });

        // if request was successfull
        createTask.done(function(data) {
            var response = JSON.parse(data);
            $('.task-added-message').text(response.message).slideDown();
            form.find('#title').val('');
            form.find('#description').val('');

            appendNewTask(response.resource);

        });

        // if request failed
        createTask.fail(function(data) {
            var response = JSON.parse(data.responseText);
            $('.validation-errors').text(response.message).slideDown();
        });

    });

    $('.task-list').on('click', '.finish-task', function(e) {
        e.preventDefault();
        var finishTaskLink = $(this);

        var finishTask = $.ajax({
            url: finishTaskLink.attr('href'),
            method: 'GET'
        });

        finishTask.done(function(data) {
            var response = JSON.parse(data);
            $('.task-added-message').text(response.message).slideDown();

            removeFinishedTask(response.resource);
        });
    });

    function appendNewTask(task) {
        /*
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><?php echo $task['title']; ?></h5>
                <p class="card-text"><?php echo $task['description']; ?></p>
                <a href="finish_task.php?position=<?php echo $index ?>" class="btn btn-primary">Finished</a>
            </div>
        </div>
        */

        var card = $('<div></div>').addClass('card').attr('id', 'task-' + task.id),
            cardBody = $('<div></div>').addClass('card-body'),
            title = $('<h5></h5>').addClass('card-title').text(task.title),
            description = $('<p></p>').addClass('card-text').text(task.description),
            action = $('<a></a>').attr('href', 'finish_task.php?position=' + task.id).addClass('btn btn-primary finish-task').text('Finished');

        var newTaskElement = card.append(cardBody.append(title).append(description).append(action));
        $('.task-list').append(newTaskElement);
    }

    function removeFinishedTask(task) {
        $('#task-' + task.id).slideUp();
    }

});


</script>

</body>
</html>
