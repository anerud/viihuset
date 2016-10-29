USE viihuset_se;

INSERT INTO vih2_module
SELECT
    m.name,
    b.name AS 'brf',
    m.title,
    m.description,
    m.sortindex,
    m.rightcol_sortindex,
    m.userlevel,
    m.userdefined,
    m.visible
FROM vih2_brf b, (
    SELECT
        'mailings' AS 'name',
        'Mailutskick' AS 'title',
        'Här kan du skicka mail till medlemmar i bostadsrättsföreningen och styrelsen' AS 'description',
        7 AS 'sortindex',
        0 AS 'rightcol_sortindex',
        'board_member' AS 'userlevel',
        0 AS 'userdefined',
        1 AS 'visible'
) m
WHERE b.name != 'sysadmin';
