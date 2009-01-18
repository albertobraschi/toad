<?php 

/**
 * class UserController
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @since    0.1
 */

class UserController extends Controller
{

    function __construct()
    {
        AuthUser::load();
        if (! AuthUser::isLoggedIn()) {
            redirect(get_url('login'));            
        }
        
        $this->setLayout('backend');
        $this->assignToLayout('sidebar', new View('user/sidebar'));
    }
    
    function index()
    {
        if (! AuthUser::hasPermission('administrator')) {
            Flash::set('error', __('You do not have permission to access the requested page!'));
            redirect(get_url());
        }
        
        $this->display('user/index', array(
            'users' => User::find()
        ));
    }
    
    function add()
    {
        if (! AuthUser::hasPermission('administrator')) {
            Flash::set('error', __('You do not have permission to access the requested page!'));
            redirect(get_url());
        }
        
        // check if trying to save
        if (get_request_method() == 'POST') {
            return $this->_add();            
        }
        
        // check if user have already enter something
        $user = Flash::get('post_data');
        
        if (empty($user)) {
            $user = new User;            
        }
        
        $this->display('user/edit', array(
            'action' => 'add',
            'user' => $user,
            'permissions' => Record::findAllFrom('Permission')
        ));
    }
    
    function _add()
    {
        $data = $_POST['user'];
        
        Flash::set('post_data', (object) $data);
        
        // check if pass and confirm are egal and >= 5 chars
        if (strlen($data['password']) >= 5 && $data['password'] == $data['confirm']) {
            $data['password'] = sha1($data['password']);
            unset($data['confirm']);
        } else {
            Flash::set('error', __('Password and Confirm are not the same or too small!'));
            redirect(get_url('user/add'));
        }
        
        // check if username >= 3 chars
        if (strlen($data['username']) < 3) {
            Flash::set('error', __('Username must contain a minimum of 3 characters!'));
            redirect(get_url('user/add'));
        }
        
        $user = new User($data);
        
        if ($user->save()) {
            // now we need to add permissions if needed
            if ( ! empty($_POST['user_permission'])) {
                UserPermission::setPermissionsFor($user->id, $_POST['user_permission']);                
            }
            
            Flash::set('success', __('User has been added!'));
        } else {
            Flash::set('error', __('User has not been added!'));
        }
        
        redirect(get_url('user'));
    }
    
    function edit($id)
    {
        if ( AuthUser::getId() != $id && ! AuthUser::hasPermission('administrator')) {
            Flash::set('error', __('You do not have permission to access the requested page!'));
            redirect(get_url());
        }
        
        // check if trying to save
        if (get_request_method() == 'POST') {
            return $this->_edit($id);            
        }
        
        if ($user = User::findById($id)) {
            $this->display('user/edit', array(
                'action' => 'edit', 
                'user' => $user,
                'permissions' => Record::findAllFrom('Permission')
            ));
        } else {
            Flash::set('error', __('User not found!'));
        }
        
        redirect(get_url('user'));
        
    } // edit
    
    function _edit($id)
    {
        $data = $_POST['user'];
        
        // check if user want to change the password
        if (strlen($data['password']) > 0) {
            // check if pass and confirm are egal and >= 5 chars
            if (strlen($data['password']) >= 5 && $data['password'] == $data['confirm']) {
                $data['password'] = sha1($data['password']);
                unset($data['confirm']);
            } else {
                Flash::set('error', __('Password and Confirm are not the same or too small!'));
                redirect(get_url('user/edit/'.$id));
            }
        } else {
            unset($data['password'], $data['confirm']);
        }
        
        $user = User::findById($id);
        $user->setFromData($data);
        
        if ($user->save()) {
            if (AuthUser::hasPermission('administrator')) {
                // now we need to add permissions
                $data = isset($_POST['user_permission']) ? $_POST['user_permission']: array();
                UserPermission::setPermissionsFor($user->id, $data);
            }
            
            Flash::set('success', __('User has been saved!'));
        } else {
            Flash::set('error', __('User has not been saved!'));
        }
        
        if (AuthUser::getId() == $id) {
            redirect(get_url('user/edit/'.$id));            
        } else {
            redirect(get_url('user'));            
        }
        
    }
    
    function delete($id)
    {
        if (! AuthUser::hasPermission('administrator')) {
            Flash::set('error', __('You do not have permission to access the requested page!'));
            redirect(get_url());
        }
        
        // security (dont delete the first admin)
        if ($id > 1) {
            // find the user to delete
            if ($user = User::findById($id)) {
                if ($user->delete()) {
                    Flash::set('success', __('User <strong>:name</strong> has been deleted!', array(':name' => $user->name)));                    
                } else {
                    Flash::set('error', __('User <strong>:name</strong> has not been deleted!', array(':name' => $user->name)));                    
                }
            } else {
                Flash::set('error', __('User not found!'));                
            }
        } else {
            Flash::set('error', __('Action disabled!'));
        }
        
        redirect(get_url('user'));
    }

}