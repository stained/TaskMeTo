<html>
<head>
    <link rel="stylesheet" type="text/css" href="/css/site.css">
</head>
<body>
    <div class="header">
        <a href="/">TaskMeTo</a>
    </div>

    <div class="nav_bar">
        <?php echo $this->render('nav.htm',$this->mime,get_defined_vars(),0); ?>
    </div>

    <div class="content">
        <?php if ($error): ?>
            <div class="error">
                <?php echo $error; ?>

            </div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="message">
                <?php echo $message; ?>

            </div>
        <?php endif; ?>

        <?php echo $this->render($content,$this->mime,get_defined_vars(),0); ?>
    </div>
</body>
</html>
