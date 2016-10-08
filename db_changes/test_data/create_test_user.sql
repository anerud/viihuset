use viihuset_se;

-- vih2_brf
INSERT INTO `vih2_brf` (`name`, `original_name`, `email`, `brfAddress`, `brfPostal`, `visitAddress`, `visitPostal`, `city`, designPattern) VALUES
('sebrf', 'SE BRF', 'seb@seb.com', 'där', '12345', 'här', '54321', 'Stöckhölm city', 1);

-- vih2_banner
INSERT INTO vih2_banner (brf, bannerLink, bannerText, font, fontSize, textColor, shadow, textAlign) VALUES
(
    'sebrf',
    'http://bostadertillsalu.se/wp-content/uploads/2013/04/Att-s%C3%A4lja-hus.jpg',
    'Sebbes bostadsrättsförening',
    'Roboto',
    28,
    'F0F0F0',
    true,
    'center'
);

-- vih2_booking_object
INSERT INTO `vih2_booking_object` (`id`, `brf`, `color`, `name`, `description`, `notifyBoard`, `sendConfirmation`, `confirmationMessage`) VALUES
(1, 'sebrf', 'FF0000', 'Bastu', '<p>H&auml;r kan du basta och k&auml;ka pasta.&nbsp;</p>', 1, 1, 'Du har nu bokat bastun!'),
(7, 'sebrf', '00FF00', 'Bastun', '<p>H&auml;r kan du basta och k&auml;ka pasta.&nbsp;</p>', 1, 1, 'Du har nu bokat bastun!'),
(8, 'sebrf', '0000FF', 'Farstu', '<p>Som bastu fast med F!</p>', 1, 1, 'Hej gudrun!'),
(9, 'sebrf', 'FFFF00', 'Duschen', '<p>Tappa inte tv&aring;len!</p>', 1, 0, 'ASDFHEJ123');

-- vih2_booking
INSERT INTO `vih2_booking` (`id`, `bookingObject`, `firstName`, `lastName`, `email`, `phone`, `apartment`, `start`, `end`, `message`, `accepted`) VALUES
(1, 1, 'Sebastian', 'Ånerud', 'seb@seb.se', '123498923', '1337', '2015-11-10 16:00:00', '2015-11-20 21:00:00', 'Jäääävlar vad det ska bastas!', 1),
(2, 8, 'Sebastian', 'Ånerud', 'seb@seb.com', '123456', '1102', '2015-10-10 13:00:00', '2015-10-15 14:00:00', 'Ska softa med gudrun i farstun!', 1),
(3, 7, 'ASDF', 'ASDf123', 'asdasdas', '10239123', '123123', '2015-10-12 00:00:00', '2015-10-25 00:00:00', 'asd123', 1),
(4, 1, 'adolf', 'denstore', 'asdasd@asdasd.com', '097123123123', '123123', '2015-10-12 00:00:00', '2015-10-15 00:00:00', 'Nu ska det bastas!!!', 1),
(5, 7, 'Adolf', 'TheGreat', 'adolf@adolfsson.com', '0123182389123', '1023', '2015-10-30 15:00:00', '2015-11-01 16:00:00', 'HajHaj', 1);

-- vih2_messageboard_thread
INSERT INTO `vih2_messageboard_thread` (`id`, `brf`, `title`, `message`, `poster`, `email`, `posted`, `userlevel`, `removed`) VALUES
(1, 'sebrf', 'Körv e la gött?!', 'Visst e la körv gött va?!', 'Sebbe', 'sebbe@sebbe.com', '2015-10-04 11:55:18', 'brf', 0),
(3, 'sebrf', 'asdf123', 'asdf123123123123123132131', 'asdf', 'asdf', '2015-10-03 15:11:25', 'brf', 0),
(4, 'sebrf', 'asdadsadas', 'asdasdsadasda', 'asda', 'asad', '2015-10-03 15:11:31', 'brf', 0),
(5, 'sebrf', 'Hej123', 'Asdfhej123', 'sebbe', 'asdf', '2015-10-04 11:36:11', 'brf', 0),
(6, 'sebrf', 'Banan', 'Va fan ska jag med en banan till?!', 'Bananen', 'bananen@guleboej.se', '2015-10-07 17:55:48', 'brf', 0),
(9, 'sebrf', 'Styrelsen endast', 'Hej alla styrelsemedlemmar!', 'Seb', 'seb@seb.se', '2015-10-09 11:03:41', 'board_member', 0),
(10, 'sebrf', 'Äpplen > Bananer', 'DVD - Det vet du!', 'Äpple', 'apple@apple.com', '2015-10-09 11:14:52', 'brf', 0),
(11, 'sebrf', 'Nu blir det styrelsemöte!', 'Välkomna till styrelsemöte den 13/14 2097 kl 25:00', 'Starke Adolf', 'hej123@asdf.com', '2015-10-14 11:35:53', 'board_member', 0);

-- vih2_messageboard_reply
INSERT INTO `vih2_messageboard_reply` (`id`, `thread`, `message`, `poster`, `email`, `posted`, `removed`) VALUES
(1, 1, 'Ja fan körv ä rektigt jävla gött alltså!', 'moset', 'mos@brickan.se', '2015-10-02 18:50:35', 0),
(2, 1, 'Ja körv me mooos!!! ELLER HAMBURGARE Ä LA GÖTT DÄ MÄ!', 'Hamburgarn', 'hmbrgn@asdf.se', '2015-10-02 20:50:14', 0),
(3, 1, 'HajHaj', 'jrögen', 'asdasd', '2015-10-03 15:17:56', 0),
(4, 1, 'asdsadasdas', 'asdasd', 'asdasd', '2015-10-03 15:31:09', 1),
(5, 1, 'Hej, mitt namn är johan, med stort J. Jag håller med.', 'Johan', 'johan@johan.com', '2015-10-13 08:51:05', 0),
(6, 1, 'jag hatar fan korv... Mer mos till folket', 'Claj', 'clajzor@claj.se', '2015-10-13 10:49:22', 0);

-- vih2_module
INSERT INTO `vih2_module` (`name`, `brf`, `title`, `description`, `userlevel`, `userdefined`, `visible`, `sortindex`, `rightcol_sortindex`) VALUES
('boardchat', 'sebrf', 'Styrelsechat', 'Skriv nåt här! (Endast medlemmar av styrelsen)', 'board_member', 0, 1, 6, null),
('photoalbum', 'sebrf', 'Fotoalbum', 'Här visas olika album med foton!', 'brf', 0, 1, 3, null),
('booking', 'sebrf', 'Boka tid', 'Här kan du boka olika bokningsobjekt!', 'brf', 0, 1, 4, 0),
('document', 'sebrf', 'Dokument', 'HEMLIGA DOKUMENT!!!', 'brf', 0, 1, 5, null),
('home', 'sebrf', 'Hem', '<p style="text-align: center;">V&auml;lkommen till v&aring;r bostadsr&auml;ttsf&ouml;rening!</p>
<p style="text-align: left;">&nbsp;</p>
<p><img style="display: block; margin-left: auto; margin-right: auto;" src="https://glendafors.files.wordpress.com/2011/05/1323932641_536e09e113-1.jpg" alt="Bild" width="500" height="340" /></p>', 'brf', 0, 1, 0, null),
('messageboard', 'sebrf', 'Anslagstavla', '<p style="text-align: left;">Skriv n&aring;got h&auml;r! Eller inte...</p>', 'brf', 0, 1, 2, 2),
('news', 'sebrf', 'Nyheter', '<p>H&auml;r kan du l&auml;sa nyheter!</p>', 'brf', 0, 1, 1, 1),
('gardsstadning', 'sebrf', 'Gårdsstädning', 'Här är info om gårdsstädning.', 'brf', 1, 1, 7, null),
('information', 'sebrf', 'Information', 'Här är allmän information.', 'brf', 1, 1, 8, null);

INSERT INTO `vih2_sub_module` VALUES
	('formaklaren', 'home', 'sebrf', 'För mäklaren', 'Haer har du infon.', 'brf', 1, 0, default),
	('fordinmamma', 'home', 'sebrf', 'För din mamma', 'Haer har du din mamma.', 'brf', 1, 0, default),
	('bajspadinfis', 'home', 'sebrf', 'Bajs på din fis', 'Den va du inte med på!!!', 'brf', 1, 0, default);

-- vih2_user
INSERT INTO `vih2_user` (`username`, `brf`, `password`, `email`, `firstname`, `lastname`, `userlevel`, `active`) VALUES
('sysadmin', '', '$2y$10$cOrnZoVst04wHUPsg/EubOBTGWujtzBRHxuOSBoo14KBVoawvtYWq', 'sebbe@sebbe.com', '', '', 'sysadmin', true),
('sebbe', 'sebrf', '$2y$10$cOrnZoVst04wHUPsg/EubOBTGWujtzBRHxuOSBoo14KBVoawvtYWq', 'sebbe@sebbe.com', '', '', 'admin', true),
('styrelsemedlem', 'sebrf', 'aksdhkajdhajskdbasjdbasjdbasjdbaskdasdsakasndkasdnasd', null, '', '', 'board_member', false),
('föreningsmedlem', 'sebrf', 'aö,dslaksjdaksjdhasjhdbasjkdsajdlkasdjaskjdasdasd', null, '', '', 'brf_member', false);

-- vih2_photo_album
INSERT INTO vih2_photo_album (brf, title, description) values ('sebrf','Den feta festen','Bilder från den fetaste festen någonsin');

-- vih2_photo_image
INSERT INTO vih2_photo_album_image (albumId, title, filepath, thumb, contentType) values
(1,'Hermoine Greger.png','uploads/sebrf_0.17217600-1454249513.png', 'uploads/sebrf_0.17217600-1454249513_thumb.png', 'image/png');

-- vih2_news
INSERT INTO vih2_news (
    brf,
    title,
    text,
    userlevel,
    show_period,
    show_start,
    show_to,
    show_calendar,
    show_calendar_date,
    posted,
    visible,
    removed
) VALUES (
    'sebrf',
    'big news',
    'Stora nyheter på gång!!!',
    'brf',
    false,
    null,
    null,
    true,
    '2016-01-16 00:00:00',
    default,
    true,
    false
);

INSERT INTO vih2_brf_member VALUES (
    1,
    'sebrf',
    'Adolf Den Andre',
    'Asdf@asdf.se',
    '1234123123',
    '2',
    '1201',
    'board_member',
    false
), (
    2,
    'sebrf',
    'Adolf Den Tredje',
    'Asdf@asdf.se',
    '1234123123',
    '2',
    '1201',
    'board_member',
    false
), (
    3,
    'sebrf',
    'Adolf Den Fjärde',
    'Asdf@asdf.se',
    '1234123123',
    '2',
    '1201',
    'brf_member',
    false
), (
    4,
    'sebrf',
    'Adolf Den Femte',
    'Asdf@asdf.se',
    '1234123123',
    '2',
    '1201',
    'brf_member',
    false
);

insert into vih2_document_file values (
    1,
    'sebrf',
    'JegErSnygg.png',
    'sebbe',
    1,
    0,
    '2016-01-30 13:11:54',
    'uploads/0.16156800 1454154392.png',
    'image/png',
    'brf_member'
), (
    2,
    'sebrf',
    'Asdf123.png',
    'sebbe',
    1,
    0,
    '2016-01-30 12:46:32',
    'uploads/0.97960400 1454155913.png',
    'image/png',
    'brf'
);

insert into vih2_visitor values
	("sebrf", "192.168.1.1"),
	("sebrf", "192.168.1.2"),
	("sebrf", "192.168.1.3"),
	("sebrf", "192.168.1.4");
