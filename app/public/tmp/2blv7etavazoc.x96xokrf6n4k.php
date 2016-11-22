<h1><?php echo $username; ?></h1>
<hr />

<h2>Current Tasks</h2>
<?php if ($currentTasks): ?>
    
        Current tasks go here
    
    <?php else: ?>
        <?php if ($isOwner): ?>
            
                You do not have any current tasks.
                <br /><br />
                Look for open <a href="/tasks">tasks</a>.
            
            <?php else: ?><?php echo $username; ?> does not have any current tasks.
        <?php endif; ?>
    
<?php endif; ?>

<h2>Completed Tasks</h2>
<?php if ($completedTasks): ?>
    
        Completed tasks go here
    
    <?php else: ?>
        <?php if ($isOwner): ?>
            You have not completed any tasks yet.
            <?php else: ?><?php echo $username; ?> has not completed any tasks yet.
        <?php endif; ?>
    
<?php endif; ?>

<h2>Created Tasks</h2>
<?php if ($createdTasks): ?>
    
        Created tasks go here
    
    <?php else: ?>
        <?php if ($isOwner): ?>
            
                You have not created any tasks yet.
                <br /><br />
                <a href="/task/create">Create</a> one now?
            
            <?php else: ?><?php echo $username; ?> has not created any tasks yet.
        <?php endif; ?>
    
<?php endif; ?>
