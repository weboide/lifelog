<?php
require(__DIR__.'/../bootstrap.php');

if(isset($_POST['submit'])) {
    if($user = checkLogin($_POST['username'], $_POST['password'])) {
        loggedin_init($user);
        redirect('/');
    }
    else {
        
    }
}
?>
<?php include(__DIR__.'/../template_header.php'); ?>
<div class="container">
    <div class="row">
        <div class="col-xs-8 col-sm-6 col-md-4 mx-auto mt-5">
            <div class="p-3 bg-body rounded shadow-sm">
                <form method="post" class="form-signin">
                    <h2 class="form-signin-heading">Please sign in</h2>
                    <label for="inputusername" class="sr-only">Email address</label>
                    <input type="text" name="username" id="inputusername" class="form-control" placeholder="Enter your username..." required autofocus>
                    <label for="inputPassword" class="sr-only">Password</label>
                    <input type="password" name="password" id="inputPassword" class="form-control" placeholder="Enter your password..." required>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" value="remember-me"> Remember me
                        </label>
                    </div>
                    <button name="submit" class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
                </form>
            </div>
        </div>
    </div> <!-- /row -->
</div> <!-- /container -->
<?php include(__DIR__.'/../template_footer.php'); ?>