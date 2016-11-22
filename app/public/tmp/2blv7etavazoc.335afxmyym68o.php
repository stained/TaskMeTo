<div class="nav">
    <?php foreach (($nav?:[]) as $key=>$value): ?>
        &#187;
        <?php if ($value): ?>
            
                <a href="<?php echo $value; ?>"><?php echo $key; ?></a>
            
            <?php else: ?>
                <?php echo $key; ?>

            
        <?php endif; ?>
    <?php endforeach; ?>
</div>
<div class="user_nav">
    <?php if ($user): ?>
        
            Logged in as <a href="/user/profile/<?php echo $user->getUsername(); ?>"><?php echo $user->getUsername(); ?></a>.
            <a href="/user/signout">Sign Out</a>
        
        <?php else: ?><a href="/user/signin">Sign In</a>
    <?php endif; ?>
</div>