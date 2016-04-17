SELECT 
	d.StartDateTime time,
	d.day,
	d.Type type,
	m.groupname name, 
	m.meeting location, 
	m.street address, 
	m.city, 
	m.state, 
	m.zipcode postal_code, 
	CONCAT_WS("<br>", m.xstreet, m.footnote1, m.footnote2, m.footnote3) notes, 
	m.boro region, 
	m.lastchange updated, 
	m.`STATUS CODE` status_code,
	CONCAT_WS("<br>", d.Type, d.SpecialInterest) types,
	m.xstreet,
	m.wc,
	m.SP
FROM MeetingDates d
JOIN Meetings m ON m.MeetingID = d.MeetingID
WHERE d.day <> "" AND m.street <> ""
#AND m.zipcode IN ("10002", "10012", "10014")
ORDER BY m.groupname