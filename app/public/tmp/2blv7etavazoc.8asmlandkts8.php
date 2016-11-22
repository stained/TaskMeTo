<p>
    Need an account?
</p>
<h1>Register</h1>

<form method="POST" action="/user/register">
    <p>
        <label for="username">Username: </label><br /><input type="text" id="username" name="username" />
    </p>
    <p>
        <label for="password">Password: </label><br /><input type="password" id="password" name="password" />
    </p>
    <p>
        <label for="email">Email: </label><br /><input type="text" id="email" name="email" />
    </p>
    <input type="submit" value="Register" />
</form>
<p>
    Already have an account? <a href="/user/signin">Sign In</a>.
</p>
