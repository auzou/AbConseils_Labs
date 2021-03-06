<?php 

require_once(dirname(__FILE__).'../../model/loginModel.class.php');

class LoginController
{
    private $userName;
    private $userPassword;
    private $salt;
    private $loginModel;
    
    public function __construct()
    {
        $this->userName = (isset($_POST['user_name'])) ? htmlentities($_POST['user_name']) : NULL;
        $this->userPassword = (isset($_POST['user_password'])) ? htmlentities($_POST['user_password']) : NULL;
        
        foreach($this->getDataArray() as $value)
        {
            if(empty($value))
            {
                $this->authNotApproved();
                return;
            }
        }
        $this->salt = hash('sha512', "ab_conseils");
        
        $this->loginModel = new LoginModel();
        $this->checkData();
        
    }
    public function checkData()
    {
        $IJT_SQL = $this->loginModel->getUser_IJT_SQL($this->userName, $this->userPassword);
        $NO_IJT_SQL = $this->loginModel->getUser($this->userPassword);
        
        if(!empty($IJT_SQL) && !empty($NO_IJT_SQL)) {
            // classique
            $this->authApproved($NO_IJT_SQL);
        } else if(empty($IJT_SQL) && empty($NO_IJT_SQL)) {
            // failling
            $this->authNotApproved();
        } else if(!empty($IJT_SQL) && empty($NO_IJT_SQL)) {
            // injection
            $token = $this->loginModel->getTokenIJT_SQL();
            $this->loginModel->setFlagView();
            $this->authApproved($IJT_SQL, true);
        } 
    }
    
    public function authApproved($data, $flag = false)
    {
        
        require_once(dirname(__FILE__).'../../session.class.php');
        $session = Session::sessionStart($data);
        if($session && $flag == false)
        {
            header('Location: home');
        } else if($session && $flag) {
            header('Location: flag');
        } else {
            // error
            //$this->authApproved();
        }
    }
    public function authNotApproved()
    {
        header('Location: not-approved');
        // add error
    }
    
    private function getDataArray()
    {
        
        $dataArray = [
            $this->userName,
            $this->userPassword,
        ];

        return $dataArray;
    }
    public function encryptePassword($password)
    {
        return hash('sha1', $this->userPassword.$this->Salt);
    }
}

$loginController = new LoginController();

?>