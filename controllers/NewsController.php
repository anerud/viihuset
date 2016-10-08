<?php
require_once("interfaces/iController.php");
require_once("interfaces/AbstractModule.php");

final class NewsController extends AbstractModule {

	private $dbContext;

	public function init($router, $dbContext){
		parent::init($router, $dbContext);

		$this->dbContext = $dbContext;

		//Brf page
		$router->map('GET', '/[a:brf]/nyheter',
			function($brf) {
				//Query database for correct info
				require_once("model/module/ModuleInfo.php");
				$module = $this->dbContext->getModule($brf,"news");
				$page = intval(isset($_GET["page"]) ? $_GET["page"] : 1);
                $date = date('Y-m-d H:i:s');
                list($maxPage, $news) = $this->dbContext->getPagedNewsByBrfAndDate($brf, $date, $page);

				// Only show news with correct userlevel
                $user = isset($_SESSION["user"])
                    ? $_SESSION["user"]
                    : null;
                $userlevel = $user != null && $user->brf == $brf
                    ? $user->userlevel
                    : "brf";


                $newsToView = array();
                foreach ($news as $n) {
                    if(UserLevels::$userLevels[$userlevel] >= UserLevels::$userLevels[$n->userlevel]) {
                        array_push($newsToView, $n);
                    }
                }

				//Render view
				$view = "NewsView";
				require_once("views/news/".$view.".php");
				$viewObject = new $view($module, $newsToView, $maxPage, getErrors());
				$viewObject->render();
			},
			NULL,
			function(){return UserLevels::$userLevels["brf"];}
		);

		$router->map('GET', '/nyheter',
			function() {
                $user = $_SESSION["user"];
                $brf = $user->brf;
                $page = intval(isset($_GET["page"]) ? $_GET["page"] : 1);
                list($maxPage, $news) = $this->dbContext->getPagedNewsByBrf($brf, $page);
				$this->showAdminModuleView($news, $maxPage, null);
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];}
		);

		//Admin page (GET)
		$router->map('GET', '/nyheter/[i:newsId]?',
			function() {
				$args = func_get_args();

				if(sizeof($args) == 0){
					require_once("model/news/News.php");
					$currentNews = new News();
				} else {
					$newsId = $args[0];
					$user = $_SESSION["user"];
					$brf = $user->brf;
					$currentNews = $this->dbContext->getNewsById($newsId, $brf);
					if($currentNews == NULL) {
                        $currentNews = new News();
                        $currentNews->id = 0;
                        $currentNews->userlevel = "brf";
					}
				}
				$this->showAdminNewsView($currentNews, getErrors());
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];}
		);

        // News portal view
        $router->map('GET', '/[a:brf]/nyheter/[i:newsId]',
			function($brf, $newsId) {
                $user = isset($_SESSION["user"])
                    ? $_SESSION["user"]
                    : null;
                $userlevel = $user != null && $user->brf == $brf
                    ? $user->userlevel
                    : "brf";

                $currentNews = $this->dbContext->getNewsById($newsId, $brf);

                if($currentNews == null ||
					UserLevels::$userLevels[$userlevel] < UserLevels::$userLevels[$currentNews->userlevel])
				{
                    header("Location: /".$brf."/nyheter");
                    return;
				}

                require_once("views/news/NewsNewsView.php");
                $view = new NewsNewsView($currentNews, getErrors());
                $view->render();
			},
			NULL,
			function(){return UserLevels::$userLevels["brf"];}
		);

        //Admin page (GET)
		$router->map('POST', '/nyheter/[i:newsId]?',
			function($newsId) {
				$user = $_SESSION["user"];
                $brf = $user->brf;

                $errors = array();

				if(!isset($_POST["title"]) || empty($_POST["title"])) {
					array_push($errors, "Du måste ange en titel på nyheten!");
				}

                if(!isset($_POST["userLevel"]) || empty($_POST["userLevel"])) {
					array_push($errors, "Du måste ange en behövrighet för nyheten!");
				}

                $title = $_POST["title"];
                $text = $_POST["text"];
                $userLevel = $_POST["userLevel"];
                $showPeriod = isset($_POST["showPeriod"]);
				$showFrom = empty($_POST["showFrom"]) ? "null" : $_POST["showFrom"];
                $showTo = empty($_POST["showTo"]) ? "null" : $_POST["showTo"];
                $showCalendar = isset($_POST["showCalendar"]);
                $showCalendarDate = empty($_POST["showCalendarDate"]) ? "null" : $_POST["showCalendarDate"];

				if(!empty($errors)) {
					setErrors($errors);
					header("Location: /nyheter/".$newsId);
					return;
				}

                require_once("model/news/News.php");
                $news = new News();

                $news->id = $newsId;
                $news->brf = $brf;
                $news->title = $title;
                $news->text = $text;
                $news->userlevel = $userLevel;
                $news->show_period = $showPeriod;
                $news->show_start = $showFrom;
                $news->show_to = $showTo;
                $news->show_calendar = $showCalendar;
                $news->show_calendar_date = $showCalendarDate;

                if($newsId == null || $newsId <= 0) {
                    $newsId = $this->dbContext->postNewNews($news);
                } else {
                    $this->dbContext->updateNews($news);
                }

                header("Location: /nyheter");

			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];}
		);

        //Admin page delete thread
		$router->map('DELETE', '/nyheter/[i:newsId]',
			function($newsId) {
				//Get the logged in user
				$user = $_SESSION["user"];
				$brf = $user->brf;
				$this->dbContext->deleteNewsById($newsId, $brf);

				header("HTTP/1.1 200 OK");
				return;
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];},
			"",
			false
		);

        $router->map('POST', '/nyheter/toggleVisibility/[i:newsId]',
			function($newsId) {
				//Get the logged in user
				$user = $_SESSION["user"];

				//Collect documents for the view
				$this->dbContext->toggleNewsVisibility($user->brf, $newsId);

				header("HTTP/1.1 200 OK");
				return;
			},
			NULL,
			function(){return UserLevels::$userLevels["admin"];},
			"",
			false
		);

	}

	/*
	* Implementing abstract methods
	*/

	public function getDomain() {
		return "portal";
	}

	public function getModuleName() {
		return "news";
	}

	public function getBaseUrl() {
		return "nyheter";
	}

	public function getLinkTitle() {
		return "Nyheter";
	}

	public function getDefaultModule() {
		require_once("model/module/ModuleInfo.php");
		$module = new ModuleInfo();
        $module->name = "news";
		$module->brf = null;
		$module->title = "Nyheter";
        $module->description = "Här visas nyheter!";
		$module->userlevel = "brf";
		$module->visible = 1;
		$module->sortindex = 1;
		$module->rightcol_sortindex = 1;
		return $module;
	}

    public function showAdminNewsView($currentNews, $errors){
		//Get the logged in user
		$user = $_SESSION["user"];

		//Query database for correct info
		require_once("model/module/ModuleInfo.php");
		$module = $this->dbContext->getModule($user->brf,"news");

		//Render view
		$view = "NewsAdminNewsView";
		require_once("views/news/".$view.".php");
        $viewObject = new $view($currentNews, $errors);
		$viewObject->render();
	}

	public function showAdminModuleView($news, $maxPage, $errors){
		//Get the logged in user
		$user = $_SESSION["user"];

		//Query database for correct info
		require_once("model/module/ModuleInfo.php");
		$module = $this->dbContext->getModule($user->brf,"news");

		//Render view
		$view = "NewsAdminView";
		require_once("views/news/".$view.".php");
        $viewObject = new $view($module, $news, $maxPage, $errors);
		$viewObject->render();
	}

	public function getRightColModule($currentBrf) {
        require_once("views/rightcol/NewsRighColView.php");
        list($lastPage, $pageResults) = $this->dbContext->getPagedNewsByBrfAndDate($currentBrf, date('Y-m-d H:i:s'), 1);
        return new NewsRighColView($pageResults, $currentBrf,$this->getDefaultModule()->name);
	}

}

?>
