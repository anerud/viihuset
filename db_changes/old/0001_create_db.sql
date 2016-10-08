use viihuset_se;

-- Drop all tables if they exists

drop table if exists vih2_booking;
drop table if exists vih2_booking_object;
drop table if exists vih2_booking_object_color;
drop table if exists vih2_document_file;
drop table if exists vih2_user;
drop table if exists vih2_sub_module;
drop table if exists vih2_module;
drop table if exists vih2_document_file;
drop table if exists vih2_messageboard_reply;
drop table if exists vih2_messageboard_thread;
drop table if exists vih2_photo_album_image;
drop table if exists vih2_photo_album;
drop table if exists vih2_banner;
drop table if exists vih2_news;
drop table if exists vih2_visitor;
drop table if exists vih2_brf_member;
drop table if exists vih2_brf;
drop table if exists vih2_design_pattern;
drop table if exists vih2_background_pattern;
drop table if exists vih2_user_level;
drop table if exists vih2_color;

create table vih2_background_pattern(
	path varchar(255) PRIMARY KEY,
    thumb varchar(255)
);

create table vih2_design_pattern(
	id int not null auto_increment PRIMARY KEY,
    name varchar(255),
	color1 char(6) not null,
	color2 char(6) not null,
    color3 char(6) not null,
    color4 char(6) not null,
	backgroundColor char(6),
	backgroundPattern varchar(255),
	preview varchar(255),
	brf varchar(255)
);

create table vih2_user_level(
	name varchar(255) PRIMARY KEY,
	level int NOT NULL,
    description varchar(255)
);

create table vih2_brf(
	name varchar(255) PRIMARY KEY,
	original_name varchar(255),
	email varchar(255),
	brfAddress varchar(255),
	brfPostal varchar(255),
	visitAddress varchar(255),
	visitPostal varchar(255),
	city varchar(255),
	show_right_col boolean default 1,
	designPattern int,
	domain_name varchar(255),
	activated boolean default false,
	registered_at timestamp default CURRENT_TIMESTAMP,
	foreign key (designPattern) references vih2_design_pattern(id)
);

create table vih2_visitor(
	brf varchar(255),
	ip varchar(255),
	foreign key (brf) references vih2_brf(name),
	primary key (brf, ip)
);

create table vih2_brf_member(
    id int not null auto_increment PRIMARY KEY,
    brf varchar(255),
    name varchar(255),
    email varchar(255),
    phone varchar(255),
    floor varchar(255),
    apartment varchar(255),
    position varchar(255),
    removed boolean default false
);

create table vih2_banner(
	brf varchar(255) PRIMARY KEY,
	bannerLink varchar(255),
	bannerText varchar(255),
    font varchar(255),
    fontSize int,
	textColor char(6) DEFAULT 'FFFFFF',
	shadow boolean DEFAULT true,
    textAlign varchar(255),
	max_width boolean default false,
	foreign key (brf) references vih2_brf(name)
);

create table vih2_user(
	username varchar(255),
	brf varchar(255),
	password varchar(255) NOT NULL,
	email varchar(255),
	firstname varchar(255),
	lastname varchar(255),
	userlevel varchar(255),
    active boolean default false,
	foreign key (userlevel) references vih2_user_level(name),
    primary key (username, brf)
);

create table vih2_messageboard_thread(
	id int not null auto_increment PRIMARY KEY,
	brf varchar(255),
	title varchar(255),
	message text,
	poster varchar(255),
	email varchar(255),
	posted timestamp default CURRENT_TIMESTAMP,
	userlevel varchar(255),
	removed boolean default false,
	foreign key (brf) references vih2_brf(name),
	foreign key (userlevel) references vih2_user_level(name)
);

create table vih2_messageboard_reply(
	id int not null auto_increment PRIMARY KEY,
	thread int,
	message text,
	poster varchar(255),
	email varchar(255),
	posted timestamp default CURRENT_TIMESTAMP,
	removed boolean default false,
	foreign key (thread) references vih2_messageboard_thread(id)
	ON DELETE CASCADE
);

create table vih2_module(
	name varchar(255),
	brf varchar(255),
	title varchar(255),
	description text,
    sortindex double NOT NULL DEFAULT '0',
    rightcol_sortindex double DEFAULT '0',
	userlevel varchar(255),
	userdefined boolean DEFAULT 0,
	visible boolean,
	foreign key (brf) references vih2_brf(name),
	primary key (name, brf),
	foreign key (userlevel) references vih2_user_level(name)
);

create table vih2_sub_module(
	name varchar(255),
	parent varchar(255),
	brf varchar(255),
	title varchar(255),
	description text,
	userlevel varchar(255),
	visible boolean,
	removed boolean default false,
	created timestamp default CURRENT_TIMESTAMP,
	foreign key (parent, brf) references vih2_module(name, brf) on delete cascade,
	primary key (name, parent, brf),
	foreign key (userlevel) references vih2_user_level(name)
);

create table vih2_document_file(
	id int not null auto_increment PRIMARY KEY,
	brf varchar(255) not null,
	title varchar(255),
	username varchar(255) not null,
	visible boolean default true,
	removed boolean default false,
	posted timestamp default CURRENT_TIMESTAMP,
	filepath varchar(255),
	extension varchar(255),
	userlevel varchar(255),
	foreign key (brf) references vih2_brf(name),
	foreign key (username) references vih2_user(username),
	foreign key (userlevel) references vih2_user_level(name)
);

create table vih2_photo_album(
	id int not null auto_increment PRIMARY KEY,
	brf varchar(255),
	title varchar(255),
	description text,
	posted timestamp default CURRENT_TIMESTAMP,
	userlevel varchar(255) default 'brf',
	visible boolean default true,
	removed boolean default false,
	unique (brf, title),
	foreign key (userlevel) references vih2_user_level(name),
	foreign key (brf) references vih2_brf(name)
);

create table vih2_photo_album_image(
	id int not null auto_increment PRIMARY KEY,
	albumId int,
	title varchar(255),
	filepath varchar(255),
    thumb varchar(255),
	contentType varchar(255),
	posted timestamp default CURRENT_TIMESTAMP,
	visible boolean default true,
	removed boolean default false,
	foreign key (albumId) references vih2_photo_album(id)
);

create table vih2_booking_object_color(
	color char(6) PRIMARY KEY
);

create table vih2_booking_object(
	id int not null auto_increment PRIMARY KEY,
	brf varchar(255),
	color char(6),
	name varchar(255),
	description text,
	notifyBoard boolean default true,
	sendConfirmation boolean default true,
	confirmationMessage text,
	foreign key (brf) references vih2_brf(name),
	foreign key (color) references vih2_booking_object_color(color)
);

create table vih2_booking(
	id int not null auto_increment PRIMARY KEY,
	bookingObject int,
	firstName varchar(255),
	lastName varchar(255),
	email varchar(255),
	phone varchar(255),
	apartment varchar(255),
	start timestamp,
	end timestamp,
	message text,
	accepted boolean default true,
	foreign key (bookingObject) references vih2_booking_object(id)
);

create table vih2_news(
    id int not null auto_increment PRIMARY KEY,
    brf varchar(255),
    title varchar(255),
    text text,
    userlevel varchar(255) default 'brf',
    show_period boolean default false,
    show_start timestamp default 0,
    show_to timestamp default 0,
    show_calendar boolean default false,
    show_calendar_date timestamp default 0,
    posted timestamp default CURRENT_TIMESTAMP,
    visible boolean default true,
    removed boolean default false,
    foreign key (brf) references vih2_brf(name)
);

create table vih2_color(
    id int not null auto_increment primary key,
    color1 char(6),
    color2 char(6),
    color3 char(6),
    color4 char(6),
    thumb varchar(255)
);

-- vih2_background_pattern
INSERT INTO vih2_background_pattern values
('/background/pattern-colors/patterns/opaque/plus.png','/background/pattern-colors/patterns/thumbs/plus_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/1_50.png','/background/pattern-colors/patterns/thumbs/1_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/2386_25920_50.png','/background/pattern-colors/patterns/thumbs/2386_25920_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/Ankare_50.png','/background/pattern-colors/patterns/thumbs/Ankare_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/Blad_50.png','/background/pattern-colors/patterns/thumbs/Blad_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/Blatriangel_50.png','/background/pattern-colors/patterns/thumbs/Blatriangel_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/Cirklar_50.png','/background/pattern-colors/patterns/thumbs/Cirklar_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/Diamant_50.png','/background/pattern-colors/patterns/thumbs/Diamant_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/India_50.png','/background/pattern-colors/patterns/thumbs/India_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/Kryss_50.png','/background/pattern-colors/patterns/thumbs/Kryss_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/Mustach_50.png','/background/pattern-colors/patterns/thumbs/Mustach_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/Pilar_50.png','/background/pattern-colors/patterns/thumbs/Pilar_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/Plattan_50.png','/background/pattern-colors/patterns/thumbs/Plattan_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/Prickar_50.png','/background/pattern-colors/patterns/thumbs/Prickar_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/Randigt_50.png','/background/pattern-colors/patterns/thumbs/Randigt_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/Rut_prickar_50.png','/background/pattern-colors/patterns/thumbs/Rut_prickar_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/Rutig2_50.png','/background/pattern-colors/patterns/thumbs/Rutig2_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/Rutig_50.png','/background/pattern-colors/patterns/thumbs/Rutig_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/Sno_50.png','/background/pattern-colors/patterns/thumbs/Sno_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/Stjarna_50.png','/background/pattern-colors/patterns/thumbs/Stjarna_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/Strack_50.png','/background/pattern-colors/patterns/thumbs/Strack_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/Tree-Top_50.png','/background/pattern-colors/patterns/thumbs/Tree-Top_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/Waves._50.png','/background/pattern-colors/patterns/thumbs/Waves._thumb.jpg'),
('/background/pattern-colors/patterns/opaque/az_subtle_50.png','/background/pattern-colors/patterns/thumbs/az_subtle_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/bright_squares_50.png','/background/pattern-colors/patterns/thumbs/bright_squares_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/diamond_upholstery_50.png','/background/pattern-colors/patterns/thumbs/diamond_upholstery_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/eight_horns_50.png','/background/pattern-colors/patterns/thumbs/eight_horns_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/elastoplast_50.png','/background/pattern-colors/patterns/thumbs/elastoplast_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/foggy_birds_50.png','/background/pattern-colors/patterns/thumbs/foggy_birds_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/gplaypattern_50.png','/background/pattern-colors/patterns/thumbs/gplaypattern_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/gradient_squares_50.png','/background/pattern-colors/patterns/thumbs/gradient_squares_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/gray_50.png','/background/pattern-colors/patterns/thumbs/gray_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/light_wool_50.png','/background/pattern-colors/patterns/thumbs/light_wool_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/linedpaper_50.png','/background/pattern-colors/patterns/thumbs/linedpaper_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/logo_x_pattern_50.png','/background/pattern-colors/patterns/thumbs/logo_x_pattern_thumb.png'),
('/background/pattern-colors/patterns/opaque/norwegian_rose_50.png','/background/pattern-colors/patterns/thumbs/norwegian_rose_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/paisley_50.png','/background/pattern-colors/patterns/thumbs/paisley_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/purty_wood_50.png','/background/pattern-colors/patterns/thumbs/purty_wood_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/tileable_wood_texture_50.png','/background/pattern-colors/patterns/thumbs/tileable_wood_texture_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/waves_v2_50.png','/background/pattern-colors/patterns/thumbs/waves_v2_thumb.jpg'),
('/background/pattern-colors/patterns/opaque/worn_dots_50.png','/background/pattern-colors/patterns/thumbs/worn_dots_thumb.png'),
('/background/pattern-colors/patterns/opaque/xv_50.png','/background/pattern-colors/patterns/thumbs/xv_thumb.jpg');



-- vih2_design_pattern
INSERT INTO vih2_design_pattern (name, color1, color2, color3, color4, backgroundColor, backgroundPattern, preview, brf) VALUES
('Aqua', 'ecfcfb', 'd5f5f2', '43cacd', '30abaa', 'FEFEFE', '/background/design-themes/aqua/bg.jpg', '/background/design-themes/aqua/preview.jpg', NULL),
('Autumn', 'fcfaec', 'fcfaec', 'f6edd8', 'e27a00', 'FEFEFE', '/background/design-themes/autumn/bg.jpg', '/background/design-themes/autumn/preview.jpg', NULL),
('Blue gray', 'f1f1f1', 'e4e4e4', '484848', '727272', 'FEFEFE', '/background/design-themes/blue-gray/bg.jpg', '/background/design-themes/blue-gray/preview.jpg', NULL),
('Chalk blue', 'f9fcff', 'e2eaf0', '4c93ae', '4c93ae', 'FEFEFE', '/background/design-themes/chalk-blue/bg.jpg', '/background/design-themes/chalk-blue/preview.jpg', NULL),
('Cherry blossom', 'ffdce4', 'fdb9c9', 'ba2668', 'ae0e55', 'FEFEFE', '/background/design-themes/cherry-blossom/bg.jpg', '/background/design-themes/cherry-blossom/preview.jpg', NULL),
('Cream', 'faf9f7', 'f3f0ec', '555c5f', '3b4043', 'FEFEFE', '/background/design-themes/cream/bg.jpg', '/background/design-themes/cream/preview.jpg', NULL),
('Funkis', 'fffce8', 'fcf4c7', 'f2aa0e', 'e7a300', 'FEFEFE', '/background/design-themes/funkis/bg.jpg', '/background/design-themes/funkis/preview.jpg', NULL),
('Green peace', 'f9faed', 'f1f3d9', 'bdd832', 'a0bc12', 'FEFEFE', '/background/design-themes/green-peace/bg.jpg', '/background/design-themes/green-peace/preview.jpg', NULL),
('Royal green', 'eff6e9', 'eff6e9', 'cee1c2', '466b20', 'FEFEFE', '/background/design-themes/royal-green/bg.jpg', '/background/design-themes/royal-green/preview.jpg', NULL),
('Smooth green', 'f9faed', 'f1f3d9', 'bdd832', 'a5c309', 'FEFEFE', '/background/design-themes/smooth-green/bg.jpg', '/background/design-themes/smooth-green/preview.jpg', NULL),
('Tree top', 'fefcf2', 'f3efd0', '9a613b', '503c24', 'FEFEFE', '/background/design-themes/tree-top/bg.jpg', '/background/design-themes/tree-top/preview.jpg', NULL),
('Upper crust', 'fffcf7', 'fdf7e3', '652443', '521834', 'FEFEFE', '/background/design-themes/upper-crust/bg.jpg', '/background/design-themes/upper-crust/preview.jpg', NULL),
('Winter', 'edf9fa', 'd9eef3', '268eba', '268eba', 'FEFEFE', '/background/design-themes/winter/bg.jpg', '/background/design-themes/winter/preview.jpg', NULL);



-- vih2_user_level
insert into vih2_user_level values ("sysadmin",5, "System admin");
insert into vih2_user_level values ("admin",4, "Administratör brf");
insert into vih2_user_level values ("board_member",3, "Styrelsemedlem brf");
insert into vih2_user_level values ("brf_member",2, "Medlem brf");
insert into vih2_user_level values ("brf",1, "Inget lösenordsskydd");
insert into vih2_user_level values ("sales",0, "Säljsida");

-- vih2_booking_object_color
INSERT INTO vih2_booking_object_color (color) VALUES ('0000FF');
INSERT INTO vih2_booking_object_color (color) VALUES ('00FF00');
INSERT INTO vih2_booking_object_color (color) VALUES ('FF0000');
INSERT INTO vih2_booking_object_color (color) VALUES ('FFFF00');
INSERT INTO vih2_booking_object_color (color) VALUES ('00FFFF');

-- vih2_color
insert into vih2_color(color1, color2, color3, color4, thumb) values
('ecfcfb','d5f5f2','43cacd','30abaa','/background/pattern-colors/colors/1__thumb.jpg'),
('fcfaec','fcfaec','f6edd8','e27a00','/background/pattern-colors/colors/2__thumb.jpg'),
('eff6e9','eff6e9','cee1c2','466b20','/background/pattern-colors/colors/3__thumb.jpg'),
('f1f1f1','e4e4e4','484848','727272','/background/pattern-colors/colors/4__thumb.jpg'),
('fffce8','fcf4c7','f2aa0e','e7a300','/background/pattern-colors/colors/5__thumb.jpg'),
('f9faed','f1f3d9','bdd832','a5c309','/background/pattern-colors/colors/6__thumb.jpg'),
('edf9fa','d9eef3','268eba','268eba','/background/pattern-colors/colors/7__thumb.jpg'),
('ffdce4','fdb9c9','ba2668','ae0e55','/background/pattern-colors/colors/8__thumb.jpg'),
('fefcf2','f3efd0','9a613b','503c24','/background/pattern-colors/colors/9__thumb.jpg'),
('fffcf7','fdf7e3','652443','521834','/background/pattern-colors/colors/12__thumb.jpg'),
('faf9f7','f3f0ec','555c5f','3b4043','/background/pattern-colors/colors/13__thumb.jpg'),
('f6edef','fcdfe5','d91941','b90027','/background/pattern-colors/colors/14__thumb.jpg'),
('faf7ff','ede2ff','9f66fd','7835e7','/background/pattern-colors/colors/15__thumb.jpg'),
('f6fbff','dbefff','66b8fd','46a0eb','/background/pattern-colors/colors/16__thumb.jpg'),
('f2fff2','c8f7cb','72ce8a','2fa84f','/background/pattern-colors/colors/17__thumb.jpg');


INSERT INTO `vih2_user` (`username`, `brf`, `password`, `email`, `firstname`, `lastname`, `userlevel`, `active`) VALUES
('sysadmin', 'sysadmin', '$2y$10$cOrnZoVst04wHUPsg/EubOBTGWujtzBRHxuOSBoo14KBVoawvtYWq', '', 'sysadmin', 'sysadmin', 'sysadmin', true);
