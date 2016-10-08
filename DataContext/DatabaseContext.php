<?php

require_once("model/user/User.php");
require_once("model/brf/Brf.php");

final class DatabaseContext {

	private $nObjectsPerPage = 5;
	private $connection;
	private $host;
	private $dbuser;
	private $dbpass;
	private $dbname;
	private static $salt;

	public function __construct(){
		$config = new AppConfig();
		// db credentials
		$this->host = $config->cfg['dbhost'];
		$this->dbuser =  $config->cfg['dbuser'];
		$this->dbpass =  $config->cfg['dbpass'];
		$this->dbname =  $config->cfg['dbdata'];
		self::$salt =  $config->cfg['constsalt'];

		// Create connection
		$this->connection = new mysqli($this->host, $this->dbuser, $this->dbpass, $this->dbname);
		$this->connection->set_charset('utf8');
		// Check connection
		if ($this->connection->connect_error) {
			die("Connection failed: " . $this->connection->connect_error);
		}

	}

	public function closeConnection() {
		$this->connection->close();
	}

	public function createObjectsFromSQLResult($result,$objectName) {
		$objects = array();
		while ($row = $result->fetch_assoc()) {
			$rClass = new ReflectionClass($objectName);
			$obj = $rClass->newInstanceArgs();

			$objVars = get_object_vars($obj);

			foreach($objVars as $varName => $varValue){
				if(!empty($varName) && isset($row[$varName])){
					$obj->$varName = $row[$varName];
				}
			}
			array_push($objects,$obj);
		}
		return $objects;
	}



	/*
	----------------- \Booking\ -----------------
	*/

	public function changeBookingTimes($bookingID, $brf, $newStart, $newEnd){
		//Check that $bookingID belongs to $brf
		$bookingObject = $this->getBookingObjectByIDAndBrf($bookingID, $brf);
		if($bookingObject != NULL) {
			$sql = 'UPDATE vih2_booking SET start = ?, end = ? WHERE id = ? and brf = ?';
			$STMT =  $this->connection->prepare($sql);
			$STMT->bind_param("ssis", $newStart, $newEnd, $bookingID, $brf);
			$STMT->execute();
		}
	}

	public function createBooking(
        $bookingObject,
        $firstName,
        $lastName,
        $email,
        $phone,
        $apartment,
        $start,
        $end,
        $message,
        $accepted
    ) {
        $stmt = $this->connection->prepare(
            "insert into vih2_booking(
                bookingObject,
                firstName,
                lastName,
                email,
                phone,
                apartment,
                start,
                end,
                message,
                accepted
            ) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
		$stmt->bind_param(
            "issssssssi",
            $bookingObject,
            $firstName,
            $lastName,
            $email,
            $phone,
            $apartment,
            $start,
            $end,
            $message,
            $accepted
        );
		return $stmt->execute();
	}

	public function createBookingObject(
        $brf,
        $color,
        $name,
        $description,
        $notifyBoard,
        $sendConfirmation,
        $confirmationMessage
    ) {
		$stmt = $this->connection->prepare(
            "insert into vih2_booking_object(
                brf,
                color,
                name,
                description,
                notifyBoard,
                sendConfirmation,
                confirmationMessage
            ) values (?, ?, ?, ?, ?, ?, ?)"
        );
		$stmt->bind_param(
            "ssssiis",
            $brf,
            $color,
            $name,
            $description,
            $notifyBoard,
            $sendConfirmation,
            $confirmationMessage
        );
		$stmt->execute();
	}

	public function deleteBookingObjectByIDAndBrf($id, $brf){
		$stmt = $this->connection->prepare("delete from vih2_booking_object where id = ? and brf = ?");
		$stmt->bind_param("ss", $id, $brf);
		$stmt->execute();
	}

	public function getBookingByIDAndBrf($bookingID, $brf){
		$stmt = $this->connection->prepare("select 	b.id,
											b.bookingObject,
											bo.name as bookingObjectName,
											bo.color as bookingObjectColor,
											b.firstName,
											b.lastName,
											b.email,
											b.phone,
											b.apartment,
											b.start,
											b.end,
											b.message,
											b.accepted
											from vih2_booking as b join vih2_booking_object as bo
											on b.bookingObject = bo.id
											where b.id = ?
											AND bo.brf = ?");
		$stmt->bind_param("is", $bookingID, $brf);
		$stmt->execute();
		$result = $stmt->get_result();
		include_once("model/booking/Booking.php");
		$bookings = $this->createObjectsFromSQLResult($result,"Booking");
		return empty($bookings) ? NULL : $bookings[0];
	}

	public function getBookingObjectByIDAndBrf($id, $brf) {
		$stmt = $this->connection->prepare("select * from vih2_booking_object where id = ? and brf = ?");
		$stmt->bind_param("ss", $id, $brf);
		$stmt->execute();
		$result = $stmt->get_result();
		include_once("model/booking/BookingObject.php");
		$bookings = $this->createObjectsFromSQLResult($result,"BookingObject");
		return empty($bookings) ? NULL : $bookings[0];
	}

	public function getBookingObjectColors() {
		$stmt = $this->connection->prepare("select * from vih2_booking_object_color");
		$stmt->execute();
		$result = $stmt->get_result();
		include_once("model/booking/BookingObjectColor.php");
		$bookingObjectColors = $this->createObjectsFromSQLResult($result, "BookingObjectColor");
		return $bookingObjectColors;
	}

	public function getPagedBookingObjectsByBrf($brf, $page) {
		$sql = "select id,color,name from vih2_booking_object where brf = ? order by id desc";
		require_once("model/booking/BookingObject.php");
		$paramTypes = "s";
		$params[] = & $paramTypes;
		$params[] = & $brf;
		return $this->paginateSQL($sql,$params,$page,"BookingObject");
	}

    public function getBookingObjectNamesAndIds($brf) {
        $stmt = $this->connection->prepare(
            "select id,name from vih2_booking_object where brf = ?"
        );
		$stmt->bind_param("s", $brf);
		$stmt->execute();
		$result = $stmt->get_result();
		include_once("model/booking/BookingObject.php");
		return $this->createObjectsFromSQLResult($result,"BookingObject");
    }

	public function getPagedBookingsByBrf($brf, $page) {
		$sql = "select 	b.id,
						b.bookingObject,
						bo.name as bookingObjectName,
						bo.color as bookingObjectColor,
	 					b.firstName,
	 					b.lastName,
	 					b.email,
						b.phone,
						b.apartment,
						b.start,
						b.end,
						b.message,
						b.accepted
						from vih2_booking as b join vih2_booking_object as bo
						on b.bookingObject = bo.id
						where bo.brf = ?
						order by b.start";

		require_once("model/booking/Booking.php");
		$paramTypes = "s";
		$params[] = & $paramTypes;
		$params[] = & $brf;
		return $this->paginateSQL($sql,$params,$page,"Booking");
	}

	public function updateBookingObjectByIDAndBrf(	$id,
													$brf,
													$color,
													$name,
													$description,
													$notifyBoard,
													$sendConfirmation,
													$confirmationMessage) {
		$stmt = $this->connection->prepare("update vih2_booking_object set
													color = ?,
													name = ?,
													description = ?,
													notifyBoard = ?,
													sendConfirmation = ?,
													confirmationMessage = ?
											where id = ? and brf = ?");
		$stmt->bind_param("sssiisis", 				$color,
													$name,
													$description,
													$notifyBoard,
													$sendConfirmation,
													$confirmationMessage,
													$id,
													$brf);
		$stmt->execute();
	}

	/*
	----------------- \Brf\ -----------------
	*/

	public function getBrfFromDomainName($domain_name) {
		$stmt = $this->connection->prepare(
			"select name, domain_name from vih2_brf where domain_name = ?"
		);
		$stmt->bind_param("s", $domain_name);
		$stmt->execute();
		$result = $stmt->get_result();
		include_once("model/brf/Brf.php");
		$brfInfo = $this->createObjectsFromSQLResult($result,"Brf");
		return empty($brfInfo) ? null : $brfInfo[0]->name;
	}

	public function getAllBrfs() {
		$stmt = $this->connection->prepare("select * from vih2_brf order by name");
		$stmt->execute();
		$result = $stmt->get_result();
		include_once("model/brf/Brf.php");
		$brfInfo = $this->createObjectsFromSQLResult($result,"Brf");
		return $brfInfo;
	}

    public function deleteBrfMember($brf, $memberId) {
        $stmt = $this->connection->prepare("
            update vih2_brf_member set removed=1
            where brf = ? AND id = ?
        ");
		$stmt->bind_param("si", $brf, $memberId);
		$stmt->execute();
    }

    public function changeBrfMemberUserLevel($brf, $memberId, $userlevel) {
        $stmt = $this->connection->prepare("
            update vih2_brf_member set position = ?
            where brf = ? AND id = ?
        ");
		$stmt->bind_param("ssi", $userlevel, $brf, $memberId);
		$stmt->execute();
    }

	public function registerBrf($name, $original_name, $email){
		$stmt = $this->connection->prepare("
			insert into vih2_brf
			(name, original_name, email, validity_period)
			values (?, ?, ?, NULL)");
		$stmt->bind_param("sss", $name, $original_name, $email);
		return $stmt->execute();
	}

	public function getBanner($brf) {
		$stmt = $this->connection->prepare("select * from vih2_banner where brf = ?");
		$stmt->bind_param("s", $brf);
		$stmt->execute();
		$result = $stmt->get_result();
		include_once("model/brf/Banner.php");
		$banner = $this->createObjectsFromSQLResult($result,"Banner");
		return empty($banner) ? NULL : $banner[0];
	}

	public function getBrfInfo($brf) {
		$stmt = $this->connection->prepare("select * from vih2_brf where name = ?");
		$stmt->bind_param("s", $brf);
		$stmt->execute();
		$result = $stmt->get_result();
		include_once("model/brf/Brf.php");
		$brfInfo = $this->createObjectsFromSQLResult($result,"Brf");
		return empty($brfInfo) ? NULL : $brfInfo[0];
	}

	public function updateBasicInfo(
		$brf,
		$email,
		$brfAddress,
		$brfPostal,
		$visitAddress,
		$visitPostal,
		$city
	) {
		$sql = "
			update vih2_brf set
			email = ?,
			brfAddress = ?,
			brfPostal = ?,
			visitAddress = ?,
			visitPostal = ?,
			city = ?
			WHERE name = ?
		";
		$STMT = $this->connection->prepare($sql);
		$STMT->bind_param(
			"sssssss",
			$email,
			$brfAddress,
			$brfPostal,
			$visitAddress,
			$visitPostal,
			$city,
			$brf
		);
		$STMT->execute();
	}

    public function createDefaultBanner($brf, $text) {
        $sql = "
            insert into vih2_banner (
                brf,
                bannerText,
                font,
                fontSize,
                textColor,
                shadow,
                textAlign
            ) values (
                ?,
                ?,
                'Roboto',
                '48',
                'F0F0F0',
                1,
                'center'
            )
        ";
        $STMT = $this->connection->prepare($sql);
		$STMT->bind_param(
			"ss",
			$brf,
            $text
		);
		$STMT->execute();
    }

	public function updateBanner($banner) {
		$sql = "
			update vih2_banner set
			bannerLink = ?,
			bannerText = ?,
			font = ?,
            fontSize = ?,
            textColor = ?,
            shadow = ?,
            textAlign = ?,
			max_width = ?
			WHERE brf = ?
		";
		$STMT = $this->connection->prepare($sql);
		$STMT->bind_param(
			"sssisisis",
			$banner->bannerLink,
            $banner->bannerText,
            $banner->font,
            $banner->fontSize,
            $banner->textColor,
            $banner->shadow,
            $banner->textAlign,
            $banner->max_width,
            $banner->brf
		);
		$STMT->execute();
	}

    public function getBrfMembers($brf) {
		$stmt = $this->connection->prepare("select * from vih2_brf_member where brf = ? and removed = false");
        $stmt->bind_param("s", $brf);
		$stmt->execute();
		$result = $stmt->get_result();
		include_once("model/brf/Member.php");
		$objects = $this->createObjectsFromSQLResult($result, "Member");
		return $objects;
	}

    public function addBrfMember($brf, $member){
		$stmt = $this->connection->prepare("
			insert into vih2_brf_member (
                brf,
                name,
                email,
                phone,
                floor,
                apartment,
                position
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?
            )
		");
		$stmt->bind_param(
            "sssssss",
            $brf,
            $member->name,
            $member->email,
            $member->phone,
            $member->floor,
            $member->apartment,
            $member->position
        );
		$stmt->execute();
	}

	public function toggleShowRightCol($brf) {
		$sql = 'UPDATE vih2_brf SET show_right_col = !show_right_col WHERE name = ?';
		$STMT =  $this->connection->prepare($sql);
		$STMT->bind_param("s", $brf);
		$STMT->execute();
	}



	/*
	----------------- \Calendar\ -----------------
	*/

	public function getBookingsByYearAndMonth($brf, $year, $month) {
		$month = strlen($month) >= 2 ? $month : "0".$month;
		$firstDay = $year."-".$month."-01 00:00:00";
		$lastDay = $year."-".$month."-31 23:59:59";

		$stmt = $this->connection->prepare("
			select
			b.id,
			bo.color as bookingObjectColor,
			b.start,
			b.end
			from vih2_booking as b join vih2_booking_object as bo
			on b.bookingObject = bo.id
			where
			bo.brf = ? AND
			b.start <= ? AND
			b.end >= ?
		");
		$stmt->bind_param("sss", $brf, $lastDay, $firstDay);
		$stmt->execute();
		$result = $stmt->get_result();
		include_once("model/booking/Booking.php");
		return $this->createObjectsFromSQLResult($result, "Booking");
	}



	/*
	----------------- \Design\ -----------------
	*/

    public function getAllColors() {
        $stmt = $this->connection->prepare("select * from vih2_color");
		$stmt->execute();
		$result = $stmt->get_result();
		include_once("model/design/Color.php");
		return $this->createObjectsFromSQLResult($result, "Color");
    }

    public function getColorById($colorId) {
        $stmt = $this->connection->prepare("select * from vih2_color where id = ?");
        $stmt->bind_param("i", $colorId);
		$stmt->execute();
		$result = $stmt->get_result();

		include_once("model/design/Color.php");
		$object = $this->createObjectsFromSQLResult($result, "Color");
        return $object == null ? null : $object[0];
    }

	public function createCustomDesignPattern($brf){
		$stmt = $this->connection->prepare("
			insert into vih2_design_pattern (
                name,
                color1,
                color2,
                color3,
                color4,
                backgroundColor,
                backgroundPattern,
                brf
            ) values (
                'Custom',
                'BCD832',
                'F8FAEE',
                'F0F4D9',
                'F0F4D9',
                'FEFEFE',
                '/background/bright-squares.png',
			    ?
            )
		");
		$stmt->bind_param("s", $brf);
		$stmt->execute();
	}

	public function getActiveDesignPatternByBrf($brf) {
		$stmt = $this->connection->prepare("
			select * from vih2_design_pattern dp join vih2_brf brf
			on dp.id = brf.designPattern
			where
			brf.name = ?
		");
		$stmt->bind_param("s", $brf);
		$stmt->execute();
		$result = $stmt->get_result();
		include_once("model/design/DesignPattern.php");
		$designPattern = $this->createObjectsFromSQLResult($result, "DesignPattern");
		return $designPattern == NULL ? $this->getDefaultDesignPattern() : $designPattern[0];
	}

	public function getBackgroundPatterns() {
		$stmt = $this->connection->prepare("select * from vih2_background_pattern");
		$stmt->execute();
		$result = $stmt->get_result();
		include_once("model/design/BackgroundPattern.php");
		return $this->createObjectsFromSQLResult($result, "BackgroundPattern");
	}

	public function getDefaultDesignPattern(){
		$stmt = $this->connection->prepare("
			select
			id, color1, color2, color3, color4, backgroundColor, backgroundPattern
			from vih2_design_pattern
			where name = 'Smooth green'
		");
		$stmt->execute();
		$result = $stmt->get_result();
		include_once("model/design/DesignPattern.php");
		$designPattern = $this->createObjectsFromSQLResult($result, "DesignPattern");
		return $designPattern == NULL ? NULL : $designPattern[0];
	}

	public function getCustomDesignPatternByBrf($brf){
		$stmt = $this->connection->prepare("select * from vih2_design_pattern where brf = ?");
		$stmt->bind_param("s", $brf);
		$stmt->execute();
		$result = $stmt->get_result();
		include_once("model/design/DesignPattern.php");
		$designPattern = $this->createObjectsFromSQLResult($result, "DesignPattern");
		return $designPattern == NULL ? NULL : $designPattern[0];
	}

	public function getAllDesignPatterns(){
		$stmt = $this->connection->prepare("
			select
			*
			from vih2_design_pattern
			where brf is null
		");
		$stmt->execute();
		$result = $stmt->get_result();
		include_once("model/design/DesignPattern.php");
		$designPatterns = $this->createObjectsFromSQLResult($result, "DesignPattern");
		return $designPatterns;
	}

	private function getDesignPatternIDByBrf($brf) {
		$stmt = $this->connection->prepare("
			select
			id
			from vih2_design_pattern join vih2_brf
			on id = designPattern
			where
			name = ?
		");
		$stmt->bind_param("s", $brf);
		$stmt->execute();
		$result = $stmt->get_result();
		$id = NULL;
		while ($row = $result->fetch_assoc()) {
			$id = $row["id"];
		}
		return $id;
	}

	public function setActiveDesignPattern($brf,$patternID) {
		$sql = 'update vih2_brf set
			designPattern = ?
			WHERE name = ?';
		$STMT =  $this->connection->prepare($sql);
		$STMT->bind_param(
			"is",
			$patternID,
			$brf
		);
		$STMT->execute();
	}

	public function updateDesignPattern(
		$design,
		$color1,
		$color2,
		$color3,
        $color4,
		$backgroundColor,
		$backgroundPattern
	) {
		$sql = 'update vih2_design_pattern set
			color1 = ?,
			color2 = ?,
			color3 = ?,
            color4 = ?,
			backgroundColor = ?,
			backgroundPattern = ?
			WHERE id = ?';
		$STMT =  $this->connection->prepare($sql);
		$STMT->bind_param(
			"ssssssi",
			$color1 == null ? $design->color1 : $color1,
			$color2 == null ? $design->color2 : $color2,
			$color3 == null ? $design->color3 : $color3,
			$color4 == null ? $design->color4 : $color4,
			$backgroundColor == null ? $design->backgroundColor : $backgroundColor,
			$backgroundPattern == null ? $design->backgroundPattern : $backgroundPattern,
			$design->id
		);
		$STMT->execute();
	}


	/*
	----------------- \Document\ -----------------
	*/

	public function addDocumentToBrf($brf) {

		$sql = "select * from vih2_document_file where brf = ? and removed = false ".($all ? "" : "and visible = true")." order by posted";
		$paramTypes = "s";
		$params[] = & $paramTypes;
		$params[] = & $brf;
		return $this->paginateSQL($sql, $params,$page,function($row){
			return $row[0];
		});
	}

	public function changeDocumentUserLevel($brf, $documentID, $level) {
		$sql = 'UPDATE vih2_document_file SET userlevel = ? WHERE id = ? and brf = ?';
		$STMT =  $this->connection->prepare($sql);
		$STMT->bind_param("sis", $level, $documentID, $brf);
		$STMT->execute();
	}

	public function deleteDocument($brf, $documentID) {
		$sql = 'update vih2_document_file set removed = 1 WHERE id = ? and brf = ?';
		$STMT =  $this->connection->prepare($sql);
		$STMT->bind_param("is", $documentID, $brf);
		$STMT->execute();
	}

	public function getDocumentByIDAndBrf($docID,$brf) {
		$stmt = $this->connection->prepare("select * from vih2_document_file where id = ? and brf = ?");
		$stmt->bind_param("is", $docID, $brf);
		$stmt->execute();
		$result = $stmt->get_result();
		include_once("model/document/Document.php");
		$documents = $this->createObjectsFromSQLResult($result,"Document");
		return empty($documents) ? NULL : $documents[0];
	}

	public function getPagedDocumentsByBrf($brf, $all, $page) {
		$sql = "select id,brf,title,posted,visible,userlevel,extension from vih2_document_file where brf = ? and removed = false ".($all ? "" : "and visible = true")." order by posted desc";
		require_once("model/document/Document.php");
		$paramTypes = "s";
		$params[] = & $paramTypes;
		$params[] = & $brf;
		return $this->paginateSQL($sql, $params,$page,"Document");
	}

	public function toggleDocumentVisibility($brf, $documentID) {
		$sql = 'UPDATE vih2_document_file SET visible = !visible WHERE id = ? and brf = ?';
		$STMT =  $this->connection->prepare($sql);
		$STMT->bind_param("is", $documentID, $brf);
		$STMT->execute();
	}

	public function uploadDocument($brf,$userName,$fileName,$filePath,$fileEnding,$rank) {
		$stmt = $this->connection->prepare("insert into vih2_document_file
			(brf, username, title, filepath, extension, userlevel)
			values (?, ?, ?, ?, ?, ?)");
		$stmt->bind_param("ssssss", $brf,$userName,$fileName,$filePath,$fileEnding,$rank);
		$stmt->execute();
	}



	/*
	----------------- \Message Board Reply\ -----------------
	*/

	public function deleteMessageBoardReply($threadID,$replyID,$brf,$rank){
		//Check that $threadID belongs to $brf
		$thread = $this->getMessageBoardThreadByIDAndRank($threadID, $rank);
		if($thread != NULL && $thread->brf != NULL && $thread->brf == $brf) {
			$stmt = $this->connection->prepare("update vih2_messageboard_reply set removed=1 where thread = ? and id = ?");
			$stmt->bind_param("ii", $threadID, $replyID);
			$stmt->execute();
		}
	}

	public function getPagedMBRepliesByThreadID($threadID, $page) {
		$sql = "select * from vih2_messageboard_reply where thread = ? and removed = false order by posted";
		require_once("model/messageBoard/Reply.php");
		$paramTypes = "i";
		$params[] = & $paramTypes;
		$params[] = & $threadID;
		return $this->paginateSQL($sql, $params,$page,"Reply");
	}

	public function postNewMessageBoardReply($message, $poster, $email, $threadID){
		$stmt = $this->connection->prepare("insert into vih2_messageboard_reply (thread,message,poster,email) VALUES (?,?,?,?)");
		$stmt->bind_param("isss", $threadID, $message, $poster, $email);
		$stmt->execute();
	}



	/*
	----------------- \Message Board Thread\ -----------------
	*/

	public function getEmailsInvolvedInThread($threadId, $posterEmail) {
		$stmt = $this->connection->prepare("
			SELECT a.email FROM (
				SELECT email FROM vih2_messageboard_thread WHERE id = ? AND email != ?
				UNION
				SELECT email FROM vih2_messageboard_reply WHERE thread = ? AND email != ?
			) AS a
			WHERE a.email != '' GROUP BY a.email;"
		);
		$stmt->bind_param("isis", $threadId, $posterEmail, $threadId, $posterEmail);
		$stmt->execute();
		$result = $stmt->get_result();
		include_once("model/messageBoard/Reply.php");
		$object = $this->createObjectsFromSQLResult($result, "Reply");
		return $object;
	}

	public function deleteMessageBoardThread($threadID,$brf){
		$stmt = $this->connection->prepare("update vih2_messageboard_thread set removed=1 where id = ? and brf = ?");
		$stmt->bind_param("is", $threadID, $brf);
		$stmt->execute();
	}

	public function getMessageBoardThreadByIDAndRank($threadID, $rank) {
		$stmt = $this->connection->prepare("select * from vih2_messageboard_thread where id = ? and userlevel = ?");
		$stmt->bind_param("ss", $threadID, $rank);
		$stmt->execute();
		$result = $stmt->get_result();
		include_once("model/messageBoard/Thread.php");
		$thread = NULL;
		while ($row = $result->fetch_assoc()) {
			$thread = new Thread();
			$thread->id = $row['id'];
			$thread->brf = $row['brf'];
			$thread->title = $row['title'];
			$thread->message = $row['message'];
			$thread->poster = $row['poster'];
			$thread->email = $row['email'];
			$thread->posted = $row['posted'];
			$thread->rank = $row['userlevel'];
		}
		return $thread;
	}

	public function getPagedMBThreadsByBrfAndRank($brf, $rank ,$page) {
		$sql = "select mt.id, mt.title, mt.poster, mt.posted, (select count(*) from vih2_messageboard_reply where thread = mt.id and removed=0) as repliesCount from vih2_messageboard_thread as mt where brf = ? and userlevel = ? and removed = false order by posted desc";
		require_once("model/messageBoard/Thread.php");
		$paramTypes = "ss";
		$params[] = & $paramTypes;
		$params[] = & $brf;
		$params[] = & $rank;
		return $this->paginateSQL($sql, $params, $page,"Thread");
	}

	public function postNewMessageBoardThread($title, $message, $poster, $email, $brf, $rank){
		$stmt = $this->connection->prepare("insert into vih2_messageboard_thread (brf,title,message,poster,email,userlevel) VALUES (?,?,?,?,?,?)");
		$stmt->bind_param("ssssss", $brf, $title, $message, $poster, $email, $rank);
		$stmt->execute();
	}



	/*
	----------------- \Module\ -----------------
	*/

	public function getModule($brf,$module) {
		$stmt = $this->connection->prepare("select * from vih2_module where brf = ? and name = ?");
		$stmt->bind_param("ss", $brf, $module);
		$stmt->execute();
		$result = $stmt->get_result();
		include_once("model/module/ModuleInfo.php");
		$object = $this->createObjectsFromSQLResult($result, "ModuleInfo");
		return $object == null ? null : $object[0];
	}

	public function getAllModules($brf) {
		$stmt = $this->connection->prepare("select * from vih2_module where brf = ? order by sortindex");
		$stmt->bind_param("s", $brf);
		$stmt->execute();
		$result = $stmt->get_result();
		include_once("model/module/ModuleInfo.php");
		$object = $this->createObjectsFromSQLResult($result, "ModuleInfo");
		return $object;
	}

	public function getUserdefinedModules($brf) {
		$stmt = $this->connection->prepare("select * from vih2_module where brf = ? and userdefined = 1");
		$stmt->bind_param("s", $brf);
		$stmt->execute();
		$result = $stmt->get_result();
		include_once("model/module/ModuleInfo.php");
		$objects = $this->createObjectsFromSQLResult($result, "ModuleInfo");
		return $objects;
	}

	public function getSubModules($brf, $parent) {
		$stmt = $this->connection->prepare("select * from vih2_sub_module where brf = ? and parent = ? and removed = 0 order by created");
		$stmt->bind_param("ss", $brf, $parent);
		$stmt->execute();
		$result = $stmt->get_result();
		include_once("model/module/SubModule.php");
		$objects = $this->createObjectsFromSQLResult($result, "SubModule");
		return $objects;
	}

	public function getSubModule($brf, $parent, $submodule) {
		$stmt = $this->connection->prepare("select * from vih2_sub_module where brf = ? AND parent = ? AND name = ? AND removed = 0");
		$stmt->bind_param("sss", $brf, $parent, $submodule);
		$stmt->execute();
		$result = $stmt->get_result();
		include_once("model/module/SubModule.php");
		$object = $this->createObjectsFromSQLResult($result, "SubModule");
		return $object == null ? null : $object[0];
	}

	public function getMaxSortIndex($brf) {
		$stmt = $this->connection->prepare("select max(sortindex) as sortindex from vih2_module where brf = ?");
		$stmt->bind_param("s", $brf);
		$stmt->execute();
		$result = $stmt->get_result();
		$sortindex = 0;
		while($row = $result->fetch_assoc()) {
            $sortindex = $row["sortindex"];
		}
		return $sortindex;
	}

	public function createOrUpdateModule(
        $name,
        $brf,
        $title,
        $description,
        $sortindex,
        $userlevel,
        $userdefined,
        $visible
	) {
		$stmt = $this->connection->prepare(
            "insert into vih2_module (
                name,
                brf,
                title,
                description,
                sortindex,
                userlevel,
				userdefined,
                visible
            ) values (?,?,?,?,?,?,?,?)
            on duplicate key update
                name = values(name),
                brf = values(brf),
                title = values(title),
                description = values(description),
                sortindex = values(sortindex),
                userlevel = values(userlevel),
                userdefined = values(userdefined),
                visible = values(visible)"
        );
		$stmt->bind_param(
            "ssssisii",
            $name,
            $brf,
            $title,
            $description,
            $sortindex,
            $userlevel,
            $userdefined,
            $visible
        );
		$stmt->execute();

	}

	public function createOrUpdateSubModule(
        $name,
        $brf,
		$parent,
        $title,
        $description,
        $userlevel,
        $visible
	) {
		$stmt = $this->connection->prepare(
            "insert into vih2_sub_module (
                name,
                brf,
				parent,
                title,
                description,
                userlevel,
                visible
            ) values (?,?,?,?,?,?,?)
            on duplicate key update
                name = values(name),
                brf = values(brf),
                parent = values(parent),
                title = values(title),
                description = values(description),
                userlevel = values(userlevel),
                visible = values(visible)"
        );
		$stmt->bind_param(
            "ssssssi",
            $name,
            $brf,
			$parent,
            $title,
            $description,
            $userlevel,
            $visible
        );
		$stmt->execute();
	}

	public function toggleModuleVisibility($brf, $module) {
		$sql = 'update vih2_module set visible = CASE visible when 1 then 0 else 1 end WHERE brf = ? and name = ?';
		$STMT =  $this->connection->prepare($sql);

		$STMT->bind_param("ss", $brf, $module);
		$STMT->execute();
	}


    public function moveUpModule($brf, $module) {
		$sql = "update vih2_module as vm
                JOIN vih2_module vm2 on
                vm2.brf = vm.brf
                and
                vm2.sortindex = vm.sortindex-1
                and
                vm.brf = ?
                and
                vm.name= ?
                set
                vm.sortindex = vm2.sortindex,
                vm2.sortindex= vm.sortindex";


        $STMT =  $this->connection->prepare($sql);
		$STMT->bind_param("ss",  $brf, $module);
		$STMT->execute();
	}


    public function moveDownModule($brf, $module) {
		$sql = "update vih2_module as vm
                JOIN vih2_module vm2 on
                vm2.brf = vm.brf
                and
                vm2.sortindex = vm.sortindex+1
                and
                vm.brf = ?
                and
                vm.name= ?
                set
                vm.sortindex = vm2.sortindex,
                vm2.sortindex= vm.sortindex";


        $STMT =  $this->connection->prepare($sql);
		$STMT->bind_param("ss",  $brf, $module);
		$STMT->execute();
	}

    public function moveUpRightColModule($brf, $module) {
		$sql = "update vih2_module as vm
                JOIN vih2_module vm2 on
                vm2.brf = vm.brf
                and
                vm2.rightcol_sortindex = vm.rightcol_sortindex-1
                and
                vm.brf = ?
                and
                vm.name= ?
                set
                vm.rightcol_sortindex = vm2.rightcol_sortindex,
                vm2.rightcol_sortindex = vm.rightcol_sortindex";


        $STMT =  $this->connection->prepare($sql);
		$STMT->bind_param("ss",  $brf, $module);
		$STMT->execute();
	}


    public function moveDownRightColModule($brf, $module) {
		$sql = "update vih2_module as vm
                JOIN vih2_module vm2 on
                vm2.brf = vm.brf
                and
                vm2.rightcol_sortindex = vm.rightcol_sortindex+1
                and
                vm.brf = ?
                and
                vm.name= ?
                set
                vm.rightcol_sortindex = vm2.rightcol_sortindex,
                vm2.rightcol_sortindex = vm.rightcol_sortindex";


        $STMT =  $this->connection->prepare($sql);
		$STMT->bind_param("ss",  $brf, $module);
		$STMT->execute();
	}


	public function getVisibility($brf, $module) {
		$sql = 'select visible from vih2_module WHERE brf = ? and name = ?';
		$STMT =  $this->connection->prepare($sql);

		$STMT->bind_param("ss", $brf, $module);
		$STMT->execute();
		$result = $STMT->get_result();

        while ($row = $result->fetch_assoc()) {
			return $row["visible"];
		}
        return false;
	}

	public function deleteSubModule($brf, $parent, $submodule) {
		$sql = 'update vih2_sub_module set removed = 1 where brf = ? and parent = ? and name = ?';
		$STMT =  $this->connection->prepare($sql);
		$STMT->bind_param("sss", $brf, $parent, $submodule);
		$STMT->execute();
	}



    /*
	----------------- \News\ -----------------
	*/
	public function getNewsById($newsId, $brf) {
		$stmt = $this->connection->prepare("select * from vih2_news where id = ? and brf = ? and removed = false");
		$stmt->bind_param("ss", $newsId, $brf);
		$stmt->execute();
		$result = $stmt->get_result();
		include_once("model/news/News.php");
		$news = $this->createObjectsFromSQLResult($result, "News");
		return empty($news) ? NULL : $news[0];
	}

    public function postNewNews($news){
		$stmt = $this->connection->prepare("INSERT INTO vih2_news
            (brf, title, text, userlevel, show_period, show_start, show_to, show_calendar, show_calendar_date)
            VALUES
            (?,?,?,?,?,?,?,?,?)"
        );
		$stmt->bind_param("ssssissis",
            $news->brf,
            $news->title,
            $news->text,
            $news->userlevel,
            $news->show_period,
            $news->show_start,
            $news->show_to,
            $news->show_calendar,
            $news->show_calendar_date
        );
		$stmt->execute();
        return $this->connection->insert_id;
	}

    public function updateNews($news){
		$stmt = $this->connection->prepare("UPDATE vih2_news set
            brf=?, title=?, text=?, userlevel=?, show_period=?, show_start=?, show_to=?, show_calendar=?, show_calendar_date=?
            WHERE id=?"
        );
		$stmt->bind_param("ssssissisi",
            $news->brf,
            $news->title,
            $news->text,
            $news->userlevel,
            $news->show_period,
            $news->show_start,
            $news->show_to,
            $news->show_calendar,
            $news->show_calendar_date,
            $news->id
        );
		$stmt->execute();
	}

    public function deleteNewsById($newsId, $brf) {
		$sql = 'update vih2_news set removed = 1 WHERE id = ? and brf = ?';
		$STMT =  $this->connection->prepare($sql);
		$STMT->bind_param("is", $newsId, $brf);
		$STMT->execute();
	}

    public function getPagedNewsByBrf($brf, $page) {
		$sql =
			"select id,title,posted,visible,userlevel
			from vih2_news
			where brf = ? and
			removed = false
			order by posted desc";
		require_once("model/news/News.php");
		$paramTypes = "s";
		$params[] = & $paramTypes;
		$params[] = & $brf;
		return $this->paginateSQL($sql,$params,$page,"News");
	}

    public function getPagedNewsByBrfAndDate($brf, $date, $page) {
		$sql =
			"select id,title,posted,visible,userlevel
			from vih2_news
			where brf = ? and
			removed = false and
            (show_start <= ? OR show_start = 0) and
            (show_to >= ? OR show_to = 0)
			order by posted desc";
		require_once("model/news/News.php");
		$paramTypes = "sss";
		$params[] = & $paramTypes;
		$params[] = & $brf;
        $params[] = & $date;
        $params[] = & $date;
		return $this->paginateSQL($sql,$params,$page,"News");
	}

    public function toggleNewsVisibility($brf, $newsId) {
		$sql = 'UPDATE vih2_news SET visible = !visible WHERE id = ? and brf = ?';
		$STMT =  $this->connection->prepare($sql);
		$STMT->bind_param("is", $newsId, $brf);
		$STMT->execute();
	}


	/*
	----------------- \Pagination\ -----------------
	*/

	private function paginateSQL($sql, $params, $page, $classname){

		$amount = $this->nObjectsPerPage;
		$selectIndex = strpos($sql, "select");
		$fromIndex = strrpos($sql, "from");

		$fromString = substr($sql, $fromIndex);
		$countSQL = "select count(*) as count ".$fromString;

		$countSTMT =  $this->connection->prepare($countSQL);

		//Bind params
		call_user_func_array(array($countSTMT, 'bind_param'), $params);

		$countSTMT->execute();
		$rows = $countSTMT->get_result()->fetch_assoc()["count"];

		$last = ceil($rows/$amount);
		if($last < 1){
			$last = 1;
		}

		if ($page < 1) {
			$page = 1;
		} else if ($page > $last) {
			$page = $last;
		}

		$limit = ' LIMIT ' .($page - 1) * $amount .',' .$amount;
		$fetchSQL = $sql.$limit;
		$fetchSTMT =  $this->connection->prepare($fetchSQL);

		//Bind params
		call_user_func_array(array($fetchSTMT, 'bind_param'), $params);

		$fetchSTMT->execute();
		$result = $fetchSTMT->get_result();
		$models = $this->createObjectsFromSQLResult($result,$classname);
		return array($last ,$models);
	}


	/*
	----------------- \Photo Album\ -----------------
	*/

    public function deletePhotoAlbumImage($albumId, $photoId, $brf){
		//Check that $threadID belongs to $brf
		$object = $this->getPhotoAlbumByID($albumId, $brf);
		if($object != NULL) {
			$stmt = $this->connection->prepare("update vih2_photo_album_image set removed=1 where albumId = ? and id = ?");
			$stmt->bind_param("ii", $albumId, $photoId);
			$stmt->execute();
		}
	}

	public function deletePhotoAlbum($brf, $albumID) {
		$sql = 'update vih2_photo_album set removed = 1 WHERE id = ? and brf = ?';
		$STMT =  $this->connection->prepare($sql);
		$STMT->bind_param("is", $albumID, $brf);
		$STMT->execute();
	}

	public function changePhotoAlbumUserLevel($brf, $albumID, $level) {
		$sql = 'UPDATE vih2_photo_album SET userlevel = ? WHERE id = ? and brf = ?';
		$STMT =  $this->connection->prepare($sql);
		$STMT->bind_param("sis", $level, $albumID, $brf);
		$STMT->execute();
	}

	public function getPagedPhotoAlbumsByBrf($brf, $page) {
		$sql =
			"select id,brf,title,userlevel,posted,visible
			from vih2_photo_album
			where brf = ? and
			removed = false
			order by posted desc";
		require_once("model/photoAlbum/PhotoAlbum.php");
		$paramTypes = "s";
		$params[] = & $paramTypes;
		$params[] = & $brf;
		return $this->paginateSQL($sql,$params,$page,"Document");
	}

	public function getPhotoAlbumByID($albumID, $brf) {
		$stmt = $this->connection->prepare("select * from vih2_photo_album where id = ? and brf = ? and removed = false");
		$stmt->bind_param("ss", $albumID, $brf);
		$stmt->execute();
		$result = $stmt->get_result();
		include_once("model/photoAlbum/PhotoAlbum.php");
		$photoAlbum = $this->createObjectsFromSQLResult($result,"PhotoAlbum");
		return empty($photoAlbum) ? NULL : $photoAlbum[0];
	}

	public function getPhotoAlbumImagesByAlbumID($albumID) {
		if($albumID == null) {
			return null;
		}
		$stmt = $this->connection->prepare("select * from vih2_photo_album_image where albumId = ? and removed = false");
		$stmt->bind_param("i", $albumID);
		$stmt->execute();
		$result = $stmt->get_result();
		include_once("model/photoAlbum/PhotoAlbumImage.php");
		$photoAlbumImages = $this->createObjectsFromSQLResult($result,"PhotoAlbumImage");
		return empty($photoAlbumImages) ? NULL : $photoAlbumImages;
	}

	public function getPhotoAlbumImage($brf, $albumID, $imageID) {
		//Check for valid (photoalbum, brf) pair.
		$photoAlbum = $this->getPhotoAlbumByID($albumID, $brf);
		if($photoAlbum == null) {
			return;
		}

		//Check if image is in given album
		$albumImages = $this->getPhotoAlbumImagesByAlbumID($albumID);
		$albumContainsImage = false;
		foreach($albumImages as $image) {
			if($image->id == $imageID) {
				$albumContainsImage = true;
				break;
			}
		}

		//If image not in album return null
		if($albumContainsImage == false) {
			return null;
		}

		//Everything ok, return image
		$stmt = $this->connection->prepare("select * from vih2_photo_album_image where id = ? and removed = false");
		$stmt->bind_param("s", $imageID);
		$stmt->execute();
		$result = $stmt->get_result();
		include_once("model/photoAlbum/PhotoAlbumImage.php");
		$photoAlbumImage = $this->createObjectsFromSQLResult($result,"PhotoAlbumImage");
		return empty($photoAlbumImage) ? NULL : $photoAlbumImage[0];
	}

	public function createPhotoAlbum($brf,$title,$description) {
		$stmt = $this->connection->prepare("
			insert into vih2_photo_album (brf, title, description)
			values
			(?,?,?)
		");
		$stmt->bind_param(
			"sss",
			$brf,
			$title,
			$description
		);
		$stmt->execute();
		return $this->connection->insert_id;
	}

	public function updatePhotoAlbum($id,$brf,$title,$description) {
		$sql = 'UPDATE vih2_photo_album SET title = ?, description = ? WHERE id = ? and brf = ? and removed = false';
		$STMT =  $this->connection->prepare($sql);
		$STMT->bind_param("ssis", $title, $description, $id, $brf);
		$STMT->execute();
	}

	public function uploadImageToPhotoAlbum(
		$brf,
		$photoAlbumID,
		$fileName,
		$filePath,
		$fileType,
        $thumb
	) {
		//Check for valid (photoalbum, brf) pair.
		$photoAlbum = $this->getPhotoAlbumByID($photoAlbumID, $brf);
		if($photoAlbum == null) {
			return;
		}
		$stmt = $this->connection->prepare("insert into vih2_photo_album_image
			(albumId, title, filePath, thumb, contentType)
			values (?, ?, ?, ?, ?)");
		$stmt->bind_param("issss", $photoAlbumID, $fileName, $filePath, $thumb, $fileType);
		$stmt->execute();
	}

	public function togglePhotoAlbumVisibility($brf, $albumID) {
		$sql = 'UPDATE vih2_photo_album SET visible = !visible WHERE id = ? and brf = ?';
		$STMT =  $this->connection->prepare($sql);
		$STMT->bind_param("is", $albumID, $brf);
		$STMT->execute();
	}


	/*
	----------------- \Site Texts\ -----------------
	*/

	public function getSiteTexts() {
		$siteTexts = $this->connection->query("select site_texts from vih_settings");
		if($siteTexts->num_rows > 0){
			$siteTexts = $siteTexts->fetch_assoc();
		}
		return $siteTexts["site_texts"];
	}



	/*
	----------------- \User\ -----------------
	*/

	public function getUserByUsernameBrfOrEmail($usernameOrEmail, $providedPassword){
		$stmt = $this->connection->prepare("select username, brf, password,
											email, firstname, lastname, userlevel, active
											from vih2_user
								  			where (email = ? or brf = ? or username = ?)");
		$stmt->bind_param("sss", $usernameOrEmail, $usernameOrEmail, $usernameOrEmail);
		$stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

		include_once("model/user/User.php");
		$users = $this->createObjectsFromSQLResult($result,"User");

		foreach ($users as $user) {
            if(password_verify($providedPassword, $user->password)) {
			 return $user;
            }
        }
		return NULL;
	}

    public function getUsers($brf){
		$stmt = $this->connection->prepare("select * from vih2_user where brf = ?");
		$stmt->bind_param("s", $brf);
		$stmt->execute();
		$result = $stmt->get_result();
		include_once("model/user/User.php");
		$objects = $this->createObjectsFromSQLResult($result,"User");
		return $objects;
	}

	public function getUserLevels() {
        require_once("enum/UserLevel.php");
		$result = $this->connection->query("select * from vih2_user_level order by level desc");
		$userLevels = array();
		while($row = $result->fetch_assoc()) {
            $userLevel = new UserLevel();
            $userLevel->name = $row["name"];
            $userLevel->level = $row["level"];
            $userLevel->description = $row["description"];
			$userLevels[$row["name"]] = $userLevel;
		}
		return $userLevels;
	}

	public function registerUser($user){
		$stmt = $this->connection->prepare(
            "insert into vih2_user (
                username,
                brf,
                password,
                email,
                firstName,
                lastName,
                userlevel,
                active
            ) values (?, ?, ?, ?, ?, ?, ?, ?)"
        );
		$user->password = password_hash($user->password, PASSWORD_BCRYPT);
		$stmt->bind_param(
            "sssssssi",
            $user->username,
            $user->brf,
            $user->password,
            $user->email,
            $user->firstName,
            $user->lastName,
            $user->userlevel,
            $user->active
        );
		return $stmt->execute();
	}

    public function updatePassword($userlevel, $providedPassword, $brf) {
        $password = password_hash($providedPassword, PASSWORD_BCRYPT);
        $stmt = $this->connection->prepare(
            "update vih2_user set password = ? where userlevel = ? and brf = ?"
        );
        $stmt->bind_param(
            "sss",
            $password,
            $userlevel,
            $brf
        );
		$stmt->execute();
    }

    public function setUserActive($userlevel, $brf, $active) {
        $stmt = $this->connection->prepare(
            "update vih2_user set active = ? where userlevel = ? and brf = ?"
        );
        $stmt->bind_param(
            "iss",
            $active,
            $userlevel,
            $brf
        );
		$stmt->execute();
    }

	public function getAdminForBrf($brf) {
		$stmt = $this->connection->prepare("select * from vih2_user where brf = ? and userlevel = 'admin'");
		$stmt->bind_param("s", $brf);
		$stmt->execute();
		$result = $stmt->get_result();
		include_once("model/user/User.php");
		$objects = $this->createObjectsFromSQLResult($result, "User");
		return empty($objects) ? NULL : $objects[0];
	}

	/*
	----------------- \Visitor\ -----------------
	*/
	public function getVisitorsByBrf($brf) {
		$stmt = $this->connection->prepare(
			"select count(ip) from vih2_visitor where brf = ?"
		);
		$stmt->bind_param("s", $brf);
		$stmt->execute();
		$result = $stmt->get_result()->fetch_assoc();
		return $result["count(ip)"];
	}

	public function saveVisitor($brf, $ip) {
		$stmt = $this->connection->prepare(
			"insert into vih2_visitor values (?, ?)"
		);
		$stmt->bind_param("ss", $brf, $ip);
		$stmt->execute();
	}
}
?>
