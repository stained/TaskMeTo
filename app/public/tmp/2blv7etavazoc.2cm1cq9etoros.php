<div class="sub_nav_bar">
    <?php if ($user): ?>
        
            <a href="/task/create">Create New Task</a> | <a href="/task/create">View Your Tasks</a>
        
        <?php else: ?><a href="/user/signin?returnpath=/tasks">Sign in</a> to create new, or view existing tasks.
    <?php endif; ?>
</div>
