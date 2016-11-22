<p>
    Already have an account?
</p>
<h1>Sign In</h1>

<form method="POST" action="/user/signin">
    <p>
        <label for="username">Username: </label><br /><input type="text" id="username" name="username" />
    </p>
    <p>
        <label for="password">Password: </label><br /><input type="password" id="password" name="password" />
    </p>
    <input type="submit" value="Sign In" />
    <input type="hidden" name="returnpath" value="<?php echo $REQUEST['returnpath']; ?>" />
</form>
<p>
    Need an account? <a href="/user/register">Register</a> one.
</p>
