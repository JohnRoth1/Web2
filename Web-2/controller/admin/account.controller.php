<?php
include_once('../../model/connect.php');
include_once('../../model/admin/account.model.php');
if (isset($_POST['function'])) {
  $function = $_POST['function'];
  switch ($function) {
    case 'edit':
      editAccount();
      break;
    case 'password':
      changePass();
      break;
    case 'resetPasswordDefault':
      resetPasswordDefault();
      break;
    case 'create':
      createAccount();
      break;
  }
}
function changePass() {
  if(isset($_POST['field'])) {
    echo passEdit($_POST['field']);
  }
}

function resetPasswordDefault() {
  if (isset($_POST['field']) && isset($_POST['field']['username'])) {
    echo passResetDefault($_POST['field']);
  }
}
function editAccount()
{
  if (isset($_POST['field'])) {
    echo account_edit($_POST['field']);
  }
}
function createAccount() {
  if (isset($_POST['field'])) {
    echo create_account($_POST['field']);
  }
}