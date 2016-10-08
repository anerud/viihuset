<?php

abstract class UserLevels {
	public static $userLevels;
    public static $namesToDescription;
	public static $initialized = false;
	
	public static function init($dbContext) {
		if(self::$initialized){
			return;	
		}
		
		$allUserLevels = $dbContext->getUserLevels();
		foreach($allUserLevels as $userLevel) {
			self::$userLevels[$userLevel->name] = $userLevel->level;
		}
        foreach($allUserLevels as $userLevel) {
			if($userLevel->level >= self::$userLevels["brf"] && $userLevel->level <= self::$userLevels["admin"]) {
                self::$namesToDescription[$userLevel->name] = $userLevel->description;
            }
		}
        
		$initialized = true;
	}
   
}

?>