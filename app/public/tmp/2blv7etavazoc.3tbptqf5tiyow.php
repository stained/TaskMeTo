<div class="sub_nav_bar">
    <?php if ($user): ?>
        
            <a href="/task/create">Create New Task</a> | <a href="/tasks/view">View Your Tasks</a>
        
        <?php else: ?><a href="/user/signin?returnpath=/tasks">Sign in</a> to create new, or view existing tasks.
    <?php endif; ?>
</div>

<h1>Open Tasks</h1>
<?php if ($openTasks): ?>
    
        Open tasks go here.
    
    <?php else: ?>
        <p>
        There are no opens tasks at present.
        </p>
        <?php if ($user): ?>
            
                <a href="/task/create">Create</a> a new task.
            
            <?php else: ?><a href="/user/signin?returnpath=/tasks">Sign in</a> to create a new task.
        <?php endif; ?>
    
<?php endif; ?>
