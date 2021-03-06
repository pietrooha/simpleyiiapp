<?php

class SiteController extends Controller
{
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
	}

	public function accessRules()
	{
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('contact'),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}	

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
		// renders the view file 'protected/views/site/index.php'
		// using the default layout 'protected/views/layouts/main.php'
		$this->render('index');
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		if($error=Yii::app()->errorHandler->error)
		{
			if(Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		}
	}

	/**
	 * Displays the contact page
	 */
	public function actionContact()
	{
		$model=new ContactForm;
		if(isset($_POST['ContactForm']))
		{
			$model->attributes=$_POST['ContactForm'];
			$model->name = Yii::app()->user->name;
			$name =$model->name;
			$model->email = Yii::app()->user->email;
			$model->id_user = Yii::app()->user->id;		

			if($model->validate())
			{
				if($model->save())
					$name='=?UTF-8?B?'.base64_encode($model->name).'?=';
					$subject='=?UTF-8?B?'.base64_encode($model->subject).'?=';
					$headers="From: $name <{$model->email}>\r\n".
						"Reply-To: {$model->email}\r\n".
						"MIME-Version: 1.0\r\n".
						"Content-Type: text/plain; charset=UTF-8";

					mail(Yii::app()->params['adminEmail'],'INFO','Pojawił się nowy kontakt. Zaloguj się, aby przeczytać',$headers);
					mail(Yii::app()->user->email, 'INFO',
						'Formularz został dostarczony do BOK',$headers);					
					Yii::app()->user->setFlash('contact','Thank you for contacting us. We will respond to you as soon as possible.');
					$this->refresh();
			}
		}
		$this->render('contact',array('model'=>$model));
	}

	/**
	 * Displays the login page
	 */
	public function actionLogin()
	{
		$model=new LoginForm;

		// if it is ajax validation request
		if(isset($_POST['ajax']) && $_POST['ajax']==='login-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		// collect user input data
		if(isset($_POST['LoginForm']))
		{
			$model->attributes=$_POST['LoginForm'];
			$message = new Message;
			// validate user input and redirect to the previous page if valid
			if($model->validate() && $model->login())
			{
				if (Yii::app()->user->getId() == '28')
				{
					$this->redirect(Yii::app()->createUrl('message/index'));
                        	}
                        	else
                        	{
                                $this->redirect(Yii::app()->createUrl('site/contact'));
                        	}
			}
		}
		// display the login form
		$this->render('login',array('model'=>$model));
	}

	/**
	 * Displays the signin page
	 */
	public function actionSignup()
	{
		$model=new SignupForm;

		if(isset($_POST['SignupForm']))
		{
			$model->attributes=$_POST['SignupForm'];

			if($model->validate())
				if($model->save())
					$this->redirect(Yii::app()->createUrl('site/login'));
		}

		$this->render('signup', array('model'=>$model));
	}
	
	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
	}
}