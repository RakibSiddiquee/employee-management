<?php
if($user->data()->isAdmin != 1){
  Redirect::to('home');
}

$db = DB::getInstance();
$validate = new Validate();

$success = '';
$errorSubject = '';
$errorEmailBody = '';

$db->get('email_templates', array('1', '=', '1'));
$getData = $db->results();

if(isset($_POST['edit'])){
  $templateId = Input::get('templateId');
  $editData = $db->get('email_templates', array('id', '=', $templateId))->first();
//var_dump($editData);
} elseif(isset($_POST['btnSave'])){
  $validation = $validate->check($_POST, array(
    'subject' => array(
      'required' => true
    ),
    'email_body' => array(
      'required' => true
    )
  ));

  $file = '';
  $uploads = '';
  if(!empty($_FILES['attachment']['name'])){
    $file = $_FILES['attachment']['name'];
  }else{
    $file = Input::get('extFile');
  }

  if($validation->passed()){
      $dir = "uploads/files/";
      //$target_dir = $dir . $base = basename($file);
      $ext = pathinfo($file, PATHINFO_EXTENSION);
      $base = basename(basename($file), '.'.$ext);
      $fileUp = $base . '_' . date('Y-m-d-H-i-s') . '_' . uniqid() . '.' . $ext;
      //var_dump($fileUp);


    try{
      $db->update('email_templates', Input::get('template_id'), array(
        'name_from' => Input::get('name_from'),
        'email_from' => Input::get('email_from'),
        'email_to' => Input::get('email_to'),
        'attachment' => $fileUp,
        'plain_text' => Input::get('plain_text'),
        'disable' => Input::get('disable'),
        'subject' => Input::get('subject'),
        'email_body' => Input::get('email_body'),
        'user_id_updated' => $user->data()->id,
        'time_updated' => date('Y-m-d H:i:s')
      ));

      move_uploaded_file($_FILES['attachment']['tmp_name'], $dir . $fileUp);

      Session::flash('success', 'Template has been updated successfully!');
      Redirect::to('email_templates');      

    } catch (Exception $e){
      die('There was a problem updating a template.');
    }
  } else {
    foreach ($validation->errors() as $error) {
      foreach ($error as $key => $value) {
        $errors[$key] = $value;
      }
    }
    if(!empty($errors['Designation'])) $errorDesignation = $errors['Designation'];

  }
}

if(Session::exists('success')){
  $success = Session::get('success');
  Session::delete('success');
}

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <section class="content-header">
    <h1 style="display: inline;">
      <?php if(!empty($editData->template_name)) {echo 'Edit ' . $editData->template_name . ' template';}else{echo 'Email templates';} ?>
    </h1>

    <?php if(!empty($success)){ ?>
    <div class="alert alert-success fade in text-center col-sm-offset-3" style="padding: 4px; width: 40%; margin-top: -30px; margin-bottom: 0;">
      <?php echo $success; ?>
      <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
    </div>
    <?php }?>

    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active"><?php echo $title; ?></li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">

    <div class="row">
      <div class="col-md-12">
        <div class="box box-info">
          <div class="box-body">
            <?php if(isset($_POST['edit'])){?>
            <form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
              <input type="hidden" name="template_id" value="<?php echo $editData->id; ?>">
              <div class="col-sm-12">
                <div class="form-group">
                  <label for="name_from" class="col-sm-2 control-label">From:</label>
                  <div class="col-sm-3" style="padding-right: 0;">
                    <input type="text" name="name_from" id="name_from" class="form-control" value="<?php echo $editData->name_from; ?>" placeholder="Name"/>
                  </div>
                  <div class="col-sm-5" style="padding-left: 5px;">
                    <input type="email" name="email_from" id="email_from" class="form-control" value="<?php echo $editData->email_from; ?>" placeholder="Email from"/>
                  </div>
                </div>
                <div class="form-group">
                  <label for="email_to" class="col-sm-2 control-label">Copy to:</label>
                  <div class="col-sm-5">
                    <input type="email" name="email_to" id="email_to" class="form-control" value="<?php echo $editData->email_to; ?>" placeholder="Template name"/>
                  </div>
                  <span>(Enter email addresses separated by a comma)</span>
                </div>
                <div class="form-group">
                  <label for="attachment" class="col-sm-2 control-label">Attachment:</label>
                  <div class="col-sm-5">
                    <input type="file" name="attachment" id="attachment" class="btn btn-default"/>
                    <input type="hidden" name="extFile" value="<?php echo $editData->attachment; ?>">
                  </div>
                </div>
                <div class="form-group">
                    <label for="plain_text" class="col-sm-2 control-label">Plain text:</label>
                    <div class="checkbox col-sm-8" style="margin-left: 20px;">
                        <input type="checkbox" name="plain_text" id="plain_text" value="1" <?php if($editData->plain_text == 1)echo 'checked';?>> Tick this box to send this email in Plain-Text format only
                    </div>
                </div>
                <div class="form-group">
                    <label for="disable" class="col-sm-2 control-label">Disable:</label>
                    <div class="checkbox col-sm-8" style="margin-left: 20px;">
                        <input type="checkbox" name="disable" id="disable" value="1" <?php if($editData->disable == 1) echo 'checked';?>> Tick this box to disable this email from being sent
                    </div>
                </div>
                <div class="form-group">
                  <label for="subject" class="col-sm-2 control-label">Subject:</label>
                  <div class="col-sm-8">
                    <input type="text" name="subject" id="subject" class="form-control" value="<?php echo $editData->subject; ?>" placeholder="Subject"/>
                  </div>
                  <small class="error col-sm-2"><?php if(!empty($errorSubject)) echo $errorSubject; ?></small>     
                </div>
                <div class="form-group">
                  <label for="email_body" class="col-sm-2 control-label">Email body:</label>
                  <div class="col-sm-8">
                    <textarea name="email_body" id="email_body" rows="10" class="form-control" placeholder="Welcome template">
                      <?php echo $editData->email_body; ?>
                    </textarea>
                  </div>
                  <small class="error col-sm-2"><?php if(!empty($errorEmailBody)) echo $errorEmailBody; ?></small>
                </div>
                <div class="form-group">
                  <div class="col-sm-2"></div>
                    <div class="col-sm-2" style="padding-right: 5px;">
                    <input type="submit" name="btnSave" value="Save changes" class="btn btn-primary form-control">
                    </div>
                    <div class="col-sm-2" style="padding-left: 0;">
                    <input type="submit" name="btnCancel" value="Cancel changes" class="btn btn-primary form-control">
                    </div>
                  
                </div>
              </div>
            </form>
            <?php }else{ ?>

            <table class="table table-bordered table-striped col-sm-offset-2" style="width: 50%;">
              <thead>
                <tr>
                  <th>Template name</th>
                  <th class="col-sm-1">Action</th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($getData as $data) { ?>
                <tr>
                  <td><?php echo $data->template_name; ?></td>
                  <td>
                    <form action="" method="post">
                      <input type="hidden" name="templateId" value="<?php echo $data->id; ?>">
                      <button type="submit" name="edit" class="btn btn-warning btn-xs" title="Edit attendance"><span class="glyphicon glyphicon-pencil"></span></button>
                    </form>
                  </td>
                </tr>
               <?php } ?>
              </tbody>
            </table>
            <?php } ?>
          </div>
        </div>

      </div><!-- /.box -->

    </div><!-- ./row -->
  </section><!-- /.content -->
</div><!-- /.content-wrapper -->
<script src="http://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.js"></script> 
<script>
jQuery(document).ready(function() {
  jQuery('#email_body').summernote({
      height: 200,
    toolbar: [
      // [groupName, [list of button]]
      ['undo', ['undo']],
      ['redo', ['redo']],
      ['style', ['bold', 'italic', 'underline', 'clear']],
      ['fontname', ['fontname']],
      ['fontsize', ['fontsize']],     
      ['font', ['strikethrough', 'superscript', 'subscript']],
      ['color', ['color']],
      ['para', ['ul', 'ol', 'paragraph']],
      ['link', ['link']],
      ['table', ['table']],
      ['hr', ['hr']],
      ['codeview', ['codeview']],
      ['fullscreen', ['fullscreen']],
      ['help', ['help']]
      ],
    placeholder: 'write here...',
  });
});

</script>

