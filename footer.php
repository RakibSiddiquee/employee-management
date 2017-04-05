      <footer class="main-footer">
        <strong>Copyright &copy; 2015-<?php echo date('Y');?> <a href="home"><?php if(!empty($settings->companyName())) echo $settings->companyName(); ?></a>.</strong> All rights reserved.
      </footer>

    <?php if($user->isLoggedIn()) echo "</div>"; ?><!-- ./wrapper -->

    <!-- jQuery 2.1.4 -->
    <script src="js/summernote.min.js"></script>

<!--    <script src="x3rdparty/plugins/jQuery/jQuery-2.1.4.min.js"></script>-->
    <!-- Bootstrap 3.3.5 -->
    <script src="3rdparty/bootstrap/js/bootstrap.min.js"></script>
    <!-- Morris.js charts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
    <script src="3rdparty/plugins/morris/morris.min.js"></script>

    <!-- AdminLTE App -->
    <script src="3rdparty/dist/js/app.min.js"></script>
    <!-- AdminLTE dashboard demo (This is only for demo purposes) -->
    <script src="3rdparty/dist/js/pages/dashboard.js"></script>
    <!-- AdminLTE for demo purposes -->
    <script src="3rdparty/dist/js/demo.js"></script>
  </body>
</html>