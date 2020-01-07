<?php die('forbidden'); ?>
1|=phpok=|0.00046|=phpok=|SELECT * FROM qinggan_tag WHERE title='公司' AND site_id='1'
1|=phpok=|0.00036|=phpok=|UPDATE qinggan_tag SET hits=hits+1 WHERE id='3'
1|=phpok=|0.00035|=phpok=|SELECT count(s.title_id) FROM qinggan_tag_stat s JOIN qinggan_list l ON(s.title_id=l.id) WHERE l.status=1 AND s.tag_id='3'
1|=phpok=|0.00033|=phpok=|SELECT title_id as id FROM qinggan_tag_stat WHERE tag_id='3'  ORDER BY title_id DESC LIMIT 0,20
1|=phpok=|0.00027|=phpok=|SELECT * FROM qinggan_cate WHERE `1`='8'
1|=phpok=|0.00038|=phpok=|SELECT * FROM qinggan_tag WHERE title='公司' AND site_id='1'
1|=phpok=|0.00025|=phpok=|UPDATE qinggan_tag SET hits=hits+1 WHERE id='3'
1|=phpok=|0.00038|=phpok=|SELECT count(s.title_id) FROM qinggan_tag_stat s JOIN qinggan_list l ON(s.title_id=l.id) WHERE l.status=1 AND s.tag_id='3'
1|=phpok=|0.00031|=phpok=|SELECT title_id as id FROM qinggan_tag_stat WHERE tag_id='3'  ORDER BY title_id DESC LIMIT 0,20
1|=phpok=|0.00019|=phpok=|SELECT * FROM qinggan_cate WHERE `1`='8'
1|=phpok=|0.00045|=phpok=|SELECT * FROM qinggan_tag WHERE title='公司' AND site_id='1'
1|=phpok=|0.00045|=phpok=|UPDATE qinggan_tag SET hits=hits+1 WHERE id='3'
1|=phpok=|0.00049|=phpok=|SELECT count(s.title_id) FROM qinggan_tag_stat s JOIN qinggan_list l ON(s.title_id=l.id) WHERE l.status=1 AND s.tag_id='3'
1|=phpok=|0.00036|=phpok=|SELECT title_id as id FROM qinggan_tag_stat WHERE tag_id='3'  ORDER BY title_id DESC LIMIT 0,20
1|=phpok=|0.00028|=phpok=|SELECT * FROM qinggan_cate WHERE `1`='8'
1|=phpok=|0.00096|=phpok=|SELECT * FROM qinggan_tag WHERE title='公司' AND site_id='1'
1|=phpok=|0.00041|=phpok=|UPDATE qinggan_tag SET hits=hits+1 WHERE id='3'
1|=phpok=|0.00134|=phpok=|SELECT count(s.title_id) FROM qinggan_tag_stat s JOIN qinggan_list l ON(s.title_id=l.id) WHERE l.status=1 AND s.tag_id='3'
1|=phpok=|0.00038|=phpok=|SELECT title_id as id FROM qinggan_tag_stat WHERE tag_id='3'  ORDER BY title_id DESC LIMIT 0,20
1|=phpok=|0.00069|=phpok=|SELECT * FROM qinggan_cate WHERE `1`='8'
1|=phpok=|0.00055|=phpok=|SELECT * FROM qinggan_tag WHERE title='公司' AND site_id='1'
1|=phpok=|0.00046|=phpok=|UPDATE qinggan_tag SET hits=hits+1 WHERE id='3'
1|=phpok=|0.00049|=phpok=|SELECT count(s.title_id) FROM qinggan_tag_stat s JOIN qinggan_list l ON(s.title_id=l.id) WHERE l.status=1 AND s.tag_id='3'
1|=phpok=|0.00036|=phpok=|SELECT title_id as id FROM qinggan_tag_stat WHERE tag_id='3'  ORDER BY title_id DESC LIMIT 0,20
1|=phpok=|0.00026|=phpok=|SELECT * FROM qinggan_cate WHERE `1`='8'
1|=phpok=|0.00049|=phpok=|SELECT * FROM qinggan_tag WHERE title='公司' AND site_id='1'
1|=phpok=|0.00034|=phpok=|UPDATE qinggan_tag SET hits=hits+1 WHERE id='3'
1|=phpok=|0.00033|=phpok=|SELECT count(s.title_id) FROM qinggan_tag_stat s JOIN qinggan_list l ON(s.title_id=l.id) WHERE l.status=1 AND s.tag_id='3'
1|=phpok=|0.00033|=phpok=|SELECT title_id as id FROM qinggan_tag_stat WHERE tag_id='3'  ORDER BY title_id DESC LIMIT 0,20
1|=phpok=|0.00042|=phpok=|SELECT * FROM qinggan_cate WHERE `id`='8'
3|=phpok=|0.00105|=phpok=|SELECT * FROM qinggan_cate WHERE `id`='7'
3|=phpok=|0.00216|=phpok=|SELECT e.*,c.content content_val FROM qinggan_fields e LEFT JOIN qinggan_extc c ON(e.id=c.id) WHERE e.ftype='cate-7' ORDER BY e.taxis asc,id DESC
1|=phpok=|0.0004|=phpok=|SELECT * FROM qinggan_fields WHERE ftype='81' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00078|=phpok=|SELECT demo FROM qinggan_cate_81 WHERE id='8'
1|=phpok=|0.00043|=phpok=|SELECT e.*,c.content content_val FROM qinggan_fields e LEFT JOIN qinggan_extc c ON(e.id=c.id) WHERE e.ftype='cate-8' ORDER BY e.taxis asc,id DESC
1|=phpok=|0.00089|=phpok=|SELECT * FROM qinggan_project WHERE 1=1  AND cate='7' AND status=1
1|=phpok=|0.00091|=phpok=|SELECT * FROM qinggan_cate WHERE site_id='1' AND status=1 ORDER BY taxis ASC,id DESC
1|=phpok=|0.00066|=phpok=|SELECT e.*,c.content content_val FROM qinggan_fields e LEFT JOIN qinggan_extc c ON(e.id=c.id) WHERE e.ftype='cate-%' ORDER BY e.taxis asc,id DESC
2|=phpok=|0.00755|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id) WHERE ext.ftype LIKE 'cate%' ORDER BY ext.taxis ASC,ext.id DESC
2|=phpok=|0.00098|=phpok=|SELECT * FROM qinggan_project WHERE id='43' AND site_id='1'
1|=phpok=|0.00043|=phpok=|SELECT * FROM qinggan_cate WHERE `id`='628'
2|=phpok=|0.00123|=phpok=|SELECT * FROM qinggan_project WHERE 1=1  AND cate='' AND status=1
1|=phpok=|0.00039|=phpok=|SELECT * FROM qinggan_cate WHERE `id`='598'
1|=phpok=|0.00045|=phpok=|SELECT * FROM qinggan_list WHERE site_id='1' AND  id='1996'  AND status=1
1|=phpok=|0.00081|=phpok=|SELECT * FROM qinggan_list_biz WHERE id='1996'
1|=phpok=|0.00089|=phpok=|SELECT * FROM qinggan_list_22 WHERE id='1996'
1|=phpok=|0.00073|=phpok=|SELECT * FROM qinggan_list_attr WHERE tid='1996' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00278|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype='list-1996' ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.0007|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='1996' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00059|=phpok=|SELECT * FROM qinggan_fields WHERE ftype='22' ORDER BY taxis ASC,id DESC
1|=phpok=|0.001|=phpok=|SELECT c.* FROM qinggan_list_cate lc JOIN qinggan_cate c ON(lc.cate_id=c.id) WHERE lc.id='1996' AND c.status=1 ORDER BY c.taxis ASC,c.id DESC
1|=phpok=|0.00283|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype IN('cate-68') ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00041|=phpok=|SELECT * FROM qinggan_list WHERE site_id='1' AND  id='1763'  AND status=1
1|=phpok=|0.00031|=phpok=|SELECT * FROM qinggan_list_biz WHERE id='1763'
1|=phpok=|0.00091|=phpok=|SELECT * FROM qinggan_list_24 WHERE id='1763'
1|=phpok=|0.00074|=phpok=|SELECT * FROM qinggan_list_attr WHERE tid='1763' ORDER BY taxis ASC,id DESC
1|=phpok=|0.001|=phpok=|SELECT * FROM qinggan_attr WHERE site_id='1' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00214|=phpok=|SELECT * FROM qinggan_attr_values  WHERE id IN(34,1,35,3,4) ORDER BY taxis ASC,id DESC LIMIT 0,999
1|=phpok=|0.00405|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype='list-1763' ORDER BY ext.taxis ASC,ext.id DESC
3|=phpok=|0.0014|=phpok=|SELECT * FROM qinggan_project WHERE id='45' AND site_id='1'
1|=phpok=|0.00062|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='1763' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00057|=phpok=|SELECT * FROM qinggan_fields WHERE ftype='24' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00088|=phpok=|SELECT * FROM qinggan_gd ORDER BY id DESC
2|=phpok=|0.00124|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1027) ORDER BY addtime DESC,id DESC LIMIT 0,999
2|=phpok=|0.00122|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1027)
1|=phpok=|0.00048|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1025,1026,1027,1028) ORDER BY addtime DESC,id DESC LIMIT 0,999
1|=phpok=|0.00047|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1028,1027,1026,1025)
1|=phpok=|0.00055|=phpok=|SELECT c.* FROM qinggan_list_cate lc JOIN qinggan_cate c ON(lc.cate_id=c.id) WHERE lc.id='1763' AND c.status=1 ORDER BY c.taxis ASC,c.id DESC
1|=phpok=|0.00317|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype IN('cate-216') ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00093|=phpok=|SELECT * FROM qinggan_site
1|=phpok=|0.00112|=phpok=|SELECT * FROM qinggan_phpok WHERE site_id='1' AND status=1
1|=phpok=|0.00054|=phpok=|SELECT * FROM qinggan_project WHERE id='42' AND site_id='1'
1|=phpok=|0.00049|=phpok=|SELECT e.*,c.content content_val FROM qinggan_fields e LEFT JOIN qinggan_extc c ON(e.id=c.id) WHERE e.ftype='project-42' ORDER BY e.taxis asc,id DESC
2|=phpok=|0.00095|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='p42' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00036|=phpok=|SELECT parent_id FROM qinggan_project WHERE id='42' AND status=1
1|=phpok=|0.00054|=phpok=|SELECT * FROM qinggan_fields WHERE ftype='23' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00047|=phpok=|SELECT count(l.id) FROM qinggan_list l  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=42  AND l.status=1
1|=phpok=|0.00133|=phpok=|SELECT l.*,ext.link,ext.target FROM qinggan_list l  JOIN qinggan_list_23 ext  ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id)  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=42  AND l.status=1   ORDER BY l.sort ASC,l.dateline DESC,l.id DESC  LIMIT 0,80
1|=phpok=|0.00279|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype IN('list-760','list-520','list-761','list-712','list-755','list-1254','list-713','list-714','list-716','list-1256','list-1298','list-1299') ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00055|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id IN('760','520','761','712','755','1254','713','714','716','1256','1298','1299') AND site_id='1' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00047|=phpok=|SELECT * FROM qinggan_list WHERE site_id='1' AND  identifier='contactus'  AND status=1
1|=phpok=|0.00025|=phpok=|SELECT * FROM qinggan_list_biz WHERE id='1757'
1|=phpok=|0.00072|=phpok=|SELECT * FROM qinggan_list_40 WHERE id='1757'
1|=phpok=|0.00028|=phpok=|SELECT * FROM qinggan_list_attr WHERE tid='1757' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00319|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype='list-1757' ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00057|=phpok=|SELECT * FROM qinggan_project WHERE id='87' AND site_id='1'
1|=phpok=|0.00046|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='1757' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00031|=phpok=|SELECT parent_id,cate_id,module_id,project_id,site_id FROM qinggan_list WHERE id='1757' AND status=1
1|=phpok=|0.00041|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='p87' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00026|=phpok=|SELECT parent_id FROM qinggan_project WHERE id='87' AND status=1
1|=phpok=|0.00042|=phpok=|SELECT * FROM qinggan_fields WHERE ftype='40' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00043|=phpok=|SELECT c.* FROM qinggan_list_cate lc JOIN qinggan_cate c ON(lc.cate_id=c.id) WHERE lc.id='1757' AND c.status=1 ORDER BY c.taxis ASC,c.id DESC
1|=phpok=|0.00044|=phpok=|SELECT e.*,c.content content_val FROM qinggan_fields e LEFT JOIN qinggan_extc c ON(e.id=c.id) WHERE e.ftype='project-45' ORDER BY e.taxis asc,id DESC
1|=phpok=|0.00042|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='p45' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00024|=phpok=|SELECT parent_id FROM qinggan_project WHERE id='45' AND status=1
1|=phpok=|0.00033|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1007) ORDER BY addtime DESC,id DESC LIMIT 0,999
1|=phpok=|0.00034|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1007)
1|=phpok=|0.00359|=phpok=|SHOW COLUMNS FROM qinggan_list
1|=phpok=|0.00055|=phpok=|SELECT count(l.id) FROM qinggan_list l  JOIN qinggan_list_24 ext  ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id)  LEFT JOIN qinggan_list_cate lc ON(l.id=lc.id)  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=45  AND l.status=1  AND l.parent_id=0  AND lc.cate_id IN(70,168,582,583,584,585,216,180,219)  AND ext.thumb != ''
1|=phpok=|0.00092|=phpok=|SELECT DISTINCT l.id,l.parent_id,l.cate_id,l.module_id,l.project_id,l.site_id,l.title,l.dateline,l.sort,l.status,l.hidden,l.hits,l.tpl,l.seo_title,l.seo_keywords,l.seo_desc,l.tag,l.attr,l.replydate,l.user_id,l.identifier,l.integral,l.style,ext.thumb,b.price,b.currency_id,b.weight,b.volume,b.unit FROM qinggan_list l  JOIN qinggan_list_24 ext  ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id)  LEFT JOIN qinggan_list_biz b ON(b.id=l.id)  LEFT JOIN qinggan_list_cate lc ON(l.id=lc.id)  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=45  AND l.status=1  AND l.parent_id=0  AND lc.cate_id IN(70,168,582,583,584,585,216,180,219)  AND ext.thumb != ''  ORDER BY l.hits DESC  LIMIT 0,5
1|=phpok=|0.00035|=phpok=|SELECT lc.id,lc.cate_id,c.title,c.identifier FROM qinggan_list_cate lc LEFT JOIN qinggan_cate c ON(lc.cate_id=c.id) WHERE lc.id IN(1763,1760,1762,1753,1761)
1|=phpok=|0.00293|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype IN('list-1763','list-1760','list-1762','list-1753','list-1761') ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00031|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1015) ORDER BY addtime DESC,id DESC LIMIT 0,999
1|=phpok=|0.00033|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1015)
1|=phpok=|0.0003|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1021) ORDER BY addtime DESC,id DESC LIMIT 0,999
1|=phpok=|0.00033|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1021)
1|=phpok=|0.0003|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1013) ORDER BY addtime DESC,id DESC LIMIT 0,999
1|=phpok=|0.00033|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1013)
1|=phpok=|0.00031|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1018) ORDER BY addtime DESC,id DESC LIMIT 0,999
1|=phpok=|0.00037|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1018)
1|=phpok=|0.00084|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id IN('1763','1760','1762','1753','1761') AND site_id='1' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00046|=phpok=|SELECT * FROM qinggan_project WHERE id='147' AND site_id='1'
1|=phpok=|0.0004|=phpok=|SELECT e.*,c.content content_val FROM qinggan_fields e LEFT JOIN qinggan_extc c ON(e.id=c.id) WHERE e.ftype='project-147' ORDER BY e.taxis asc,id DESC
1|=phpok=|0.00041|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='p147' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00027|=phpok=|SELECT parent_id FROM qinggan_project WHERE id='147' AND status=1
1|=phpok=|0.00039|=phpok=|SELECT count(l.id) FROM qinggan_list l  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=147  AND l.status=1  AND l.parent_id=0
1|=phpok=|0.00063|=phpok=|SELECT l.*,ext.link,ext.target FROM qinggan_list l  JOIN qinggan_list_23 ext  ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id)  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=147  AND l.status=1  AND l.parent_id=0   ORDER BY l.sort ASC,l.dateline DESC,l.id DESC  LIMIT 0,10
1|=phpok=|0.00303|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype IN('list-1300','list-1301','list-1302','list-1303','list-1304','list-1932') ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00081|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id IN('1300','1301','1302','1303','1304','1932') AND site_id='1' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00048|=phpok=|SELECT * FROM qinggan_project WHERE id='148' AND site_id='1'
1|=phpok=|0.00046|=phpok=|SELECT e.*,c.content content_val FROM qinggan_fields e LEFT JOIN qinggan_extc c ON(e.id=c.id) WHERE e.ftype='project-148' ORDER BY e.taxis asc,id DESC
1|=phpok=|0.00041|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='p148' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00025|=phpok=|SELECT parent_id FROM qinggan_project WHERE id='148' AND status=1
1|=phpok=|0.00048|=phpok=|SELECT * FROM qinggan_fields WHERE ftype='64' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00034|=phpok=|SELECT count(l.id) FROM qinggan_list l  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=148  AND l.status=1  AND l.parent_id=0
1|=phpok=|0.00135|=phpok=|SELECT l.*,ext.qtype,ext.qq,ext.weixin,ext.qrcode FROM qinggan_list l  JOIN qinggan_list_64 ext  ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id)  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=148  AND l.status=1  AND l.parent_id=0   ORDER BY l.sort ASC,l.dateline DESC,l.id DESC  LIMIT 0,50
1|=phpok=|0.00298|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype IN('list-1427','list-1305') ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00033|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1348) ORDER BY addtime DESC,id DESC LIMIT 0,999
1|=phpok=|0.00037|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1348)
1|=phpok=|0.00047|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id IN('1427','1305') AND site_id='1' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00088|=phpok=|SELECT * FROM qinggan_tpl WHERE id='1'
1|=phpok=|0.001|=phpok=|SELECT * FROM qinggan_plugins WHERE status=1  ORDER BY taxis ASC,id DESC
1|=phpok=|0.00049|=phpok=|SELECT * FROM qinggan_plugins WHERE id='sitecopy'
1|=phpok=|0.00058|=phpok=|SELECT * FROM qinggan_plugins WHERE id='identifier'
1|=phpok=|0.00078|=phpok=|SELECT id FROM qinggan_cart WHERE session_id='2alfhatmorcku05oioi627o875'
1|=phpok=|0.00031|=phpok=|UPDATE qinggan_cart SET `session_id`='2alfhatmorcku05oioi627o875',`user_id`='',`addtime`='1556477080' WHERE `id`='8'
1|=phpok=|0.00071|=phpok=|SELECT SUM(qty) FROM qinggan_cart_product WHERE cart_id='8'
1|=phpok=|0.00044|=phpok=|SELECT * FROM qinggan_tpl WHERE id='1'
1|=phpok=|0.00037|=phpok=|SELECT * FROM qinggan_plugins WHERE status=1  ORDER BY taxis ASC,id DESC
1|=phpok=|0.00045|=phpok=|SELECT * FROM qinggan_plugins WHERE id='sitecopy'
1|=phpok=|0.00041|=phpok=|SELECT * FROM qinggan_plugins WHERE id='identifier'
1|=phpok=|0.00097|=phpok=|UPDATE qinggan_task SET is_lock=0 WHERE exec_time<1556477076 AND exec_time>0 AND is_lock=1
1|=phpok=|0.0004|=phpok=|SELECT * FROM qinggan_task WHERE 1=1 ORDER BY id ASC
1|=phpok=|0.0004|=phpok=|SELECT * FROM qinggan_tag WHERE title='公司' AND site_id='1'
1|=phpok=|0.00192|=phpok=|UPDATE qinggan_tag SET hits=hits+1 WHERE id='3'
1|=phpok=|0.00042|=phpok=|SELECT count(s.title_id) FROM qinggan_tag_stat s JOIN qinggan_list l ON(s.title_id=l.id) WHERE l.status=1 AND s.tag_id='3'
1|=phpok=|0.00039|=phpok=|SELECT title_id as id FROM qinggan_tag_stat WHERE tag_id='3'  ORDER BY title_id DESC LIMIT 0,20
1|=phpok=|0.00039|=phpok=|SELECT * FROM qinggan_cate WHERE `id`='8'
3|=phpok=|0.00099|=phpok=|SELECT * FROM qinggan_cate WHERE `id`='7'
3|=phpok=|0.00125|=phpok=|SELECT e.*,c.content content_val FROM qinggan_fields e LEFT JOIN qinggan_extc c ON(e.id=c.id) WHERE e.ftype='cate-7' ORDER BY e.taxis asc,id DESC
1|=phpok=|0.00055|=phpok=|SELECT * FROM qinggan_fields WHERE ftype='81' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00027|=phpok=|SELECT demo FROM qinggan_cate_81 WHERE id='8'
1|=phpok=|0.00045|=phpok=|SELECT e.*,c.content content_val FROM qinggan_fields e LEFT JOIN qinggan_extc c ON(e.id=c.id) WHERE e.ftype='cate-8' ORDER BY e.taxis asc,id DESC
1|=phpok=|0.00043|=phpok=|SELECT * FROM qinggan_project WHERE 1=1  AND cate='7' AND status=1
1|=phpok=|0.00033|=phpok=|SELECT * FROM qinggan_cate WHERE `id`='628'
2|=phpok=|0.00117|=phpok=|SELECT * FROM qinggan_project WHERE 1=1  AND cate='' AND status=1
1|=phpok=|0.00079|=phpok=|SELECT * FROM qinggan_cate WHERE site_id='1' AND status=1 ORDER BY taxis ASC,id DESC
1|=phpok=|0.00048|=phpok=|SELECT e.*,c.content content_val FROM qinggan_fields e LEFT JOIN qinggan_extc c ON(e.id=c.id) WHERE e.ftype='cate-%' ORDER BY e.taxis asc,id DESC
1|=phpok=|0.00044|=phpok=|SELECT * FROM qinggan_cate WHERE `id`='598'
1|=phpok=|0.00047|=phpok=|SELECT * FROM qinggan_list WHERE site_id='1' AND  id='1996'  AND status=1
1|=phpok=|0.00026|=phpok=|SELECT * FROM qinggan_list_biz WHERE id='1996'
1|=phpok=|0.00051|=phpok=|SELECT * FROM qinggan_list_22 WHERE id='1996'
1|=phpok=|0.00053|=phpok=|SELECT * FROM qinggan_list_attr WHERE tid='1996' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00373|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype='list-1996' ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00057|=phpok=|SELECT * FROM qinggan_project WHERE id='43' AND site_id='1'
1|=phpok=|0.00081|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='1996' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00048|=phpok=|SELECT * FROM qinggan_fields WHERE ftype='22' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00069|=phpok=|SELECT c.* FROM qinggan_list_cate lc JOIN qinggan_cate c ON(lc.cate_id=c.id) WHERE lc.id='1996' AND c.status=1 ORDER BY c.taxis ASC,c.id DESC
1|=phpok=|0.00288|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype IN('cate-68') ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00046|=phpok=|SELECT * FROM qinggan_list WHERE site_id='1' AND  id='1763'  AND status=1
1|=phpok=|0.00034|=phpok=|SELECT * FROM qinggan_list_biz WHERE id='1763'
1|=phpok=|0.00042|=phpok=|SELECT * FROM qinggan_list_24 WHERE id='1763'
1|=phpok=|0.00044|=phpok=|SELECT * FROM qinggan_list_attr WHERE tid='1763' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00044|=phpok=|SELECT * FROM qinggan_attr WHERE site_id='1' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00043|=phpok=|SELECT * FROM qinggan_attr_values  WHERE id IN(34,1,35,3,4) ORDER BY taxis ASC,id DESC LIMIT 0,999
1|=phpok=|0.00342|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype='list-1763' ORDER BY ext.taxis ASC,ext.id DESC
3|=phpok=|0.00161|=phpok=|SELECT * FROM qinggan_project WHERE id='45' AND site_id='1'
1|=phpok=|0.00085|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='1763' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.0005|=phpok=|SELECT * FROM qinggan_fields WHERE ftype='24' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00037|=phpok=|SELECT * FROM qinggan_gd ORDER BY id DESC
2|=phpok=|0.00077|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1027) ORDER BY addtime DESC,id DESC LIMIT 0,999
2|=phpok=|0.00095|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1027)
1|=phpok=|0.00043|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1025,1026,1027,1028) ORDER BY addtime DESC,id DESC LIMIT 0,999
1|=phpok=|0.00041|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1028,1027,1026,1025)
1|=phpok=|0.00077|=phpok=|SELECT c.* FROM qinggan_list_cate lc JOIN qinggan_cate c ON(lc.cate_id=c.id) WHERE lc.id='1763' AND c.status=1 ORDER BY c.taxis ASC,c.id DESC
1|=phpok=|0.00314|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype IN('cate-216') ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00048|=phpok=|SELECT * FROM qinggan_site
1|=phpok=|0.00057|=phpok=|SELECT * FROM qinggan_phpok WHERE site_id='1' AND status=1
1|=phpok=|0.00044|=phpok=|SELECT * FROM qinggan_project WHERE id='42' AND site_id='1'
1|=phpok=|0.00044|=phpok=|SELECT e.*,c.content content_val FROM qinggan_fields e LEFT JOIN qinggan_extc c ON(e.id=c.id) WHERE e.ftype='project-42' ORDER BY e.taxis asc,id DESC
2|=phpok=|0.00092|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='p42' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00027|=phpok=|SELECT parent_id FROM qinggan_project WHERE id='42' AND status=1
1|=phpok=|0.00053|=phpok=|SELECT * FROM qinggan_fields WHERE ftype='23' ORDER BY taxis ASC,id DESC
1|=phpok=|0.0004|=phpok=|SELECT count(l.id) FROM qinggan_list l  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=42  AND l.status=1
1|=phpok=|0.00078|=phpok=|SELECT l.*,ext.link,ext.target FROM qinggan_list l  JOIN qinggan_list_23 ext  ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id)  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=42  AND l.status=1   ORDER BY l.sort ASC,l.dateline DESC,l.id DESC  LIMIT 0,80
1|=phpok=|0.00338|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype IN('list-760','list-520','list-761','list-712','list-755','list-1254','list-713','list-714','list-716','list-1256','list-1298','list-1299') ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00069|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id IN('760','520','761','712','755','1254','713','714','716','1256','1298','1299') AND site_id='1' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.0005|=phpok=|SELECT * FROM qinggan_list WHERE site_id='1' AND  identifier='contactus'  AND status=1
1|=phpok=|0.00027|=phpok=|SELECT * FROM qinggan_list_biz WHERE id='1757'
1|=phpok=|0.00029|=phpok=|SELECT * FROM qinggan_list_40 WHERE id='1757'
1|=phpok=|0.00029|=phpok=|SELECT * FROM qinggan_list_attr WHERE tid='1757' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00344|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype='list-1757' ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00049|=phpok=|SELECT * FROM qinggan_project WHERE id='87' AND site_id='1'
1|=phpok=|0.00044|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='1757' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00029|=phpok=|SELECT parent_id,cate_id,module_id,project_id,site_id FROM qinggan_list WHERE id='1757' AND status=1
1|=phpok=|0.00038|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='p87' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00022|=phpok=|SELECT parent_id FROM qinggan_project WHERE id='87' AND status=1
1|=phpok=|0.00083|=phpok=|SELECT * FROM qinggan_fields WHERE ftype='40' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00053|=phpok=|SELECT c.* FROM qinggan_list_cate lc JOIN qinggan_cate c ON(lc.cate_id=c.id) WHERE lc.id='1757' AND c.status=1 ORDER BY c.taxis ASC,c.id DESC
1|=phpok=|0.00064|=phpok=|SELECT e.*,c.content content_val FROM qinggan_fields e LEFT JOIN qinggan_extc c ON(e.id=c.id) WHERE e.ftype='project-45' ORDER BY e.taxis asc,id DESC
1|=phpok=|0.00053|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='p45' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00031|=phpok=|SELECT parent_id FROM qinggan_project WHERE id='45' AND status=1
1|=phpok=|0.00036|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1007) ORDER BY addtime DESC,id DESC LIMIT 0,999
1|=phpok=|0.00039|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1007)
1|=phpok=|0.00338|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id) WHERE ext.ftype LIKE 'cate%' ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00382|=phpok=|SHOW COLUMNS FROM qinggan_list
1|=phpok=|0.00066|=phpok=|SELECT count(l.id) FROM qinggan_list l  JOIN qinggan_list_24 ext  ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id)  LEFT JOIN qinggan_list_cate lc ON(l.id=lc.id)  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=45  AND l.status=1  AND l.parent_id=0  AND lc.cate_id IN(70,168,582,583,584,585,216,180,219)  AND ext.thumb != ''
1|=phpok=|0.00107|=phpok=|SELECT DISTINCT l.id,l.parent_id,l.cate_id,l.module_id,l.project_id,l.site_id,l.title,l.dateline,l.sort,l.status,l.hidden,l.hits,l.tpl,l.seo_title,l.seo_keywords,l.seo_desc,l.tag,l.attr,l.replydate,l.user_id,l.identifier,l.integral,l.style,ext.thumb,b.price,b.currency_id,b.weight,b.volume,b.unit FROM qinggan_list l  JOIN qinggan_list_24 ext  ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id)  LEFT JOIN qinggan_list_biz b ON(b.id=l.id)  LEFT JOIN qinggan_list_cate lc ON(l.id=lc.id)  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=45  AND l.status=1  AND l.parent_id=0  AND lc.cate_id IN(70,168,582,583,584,585,216,180,219)  AND ext.thumb != ''  ORDER BY l.hits DESC  LIMIT 0,5
1|=phpok=|0.00048|=phpok=|SELECT lc.id,lc.cate_id,c.title,c.identifier FROM qinggan_list_cate lc LEFT JOIN qinggan_cate c ON(lc.cate_id=c.id) WHERE lc.id IN(1763,1760,1762,1753,1761)
1|=phpok=|0.00354|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype IN('list-1763','list-1760','list-1762','list-1753','list-1761') ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00032|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1015) ORDER BY addtime DESC,id DESC LIMIT 0,999
1|=phpok=|0.00033|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1015)
1|=phpok=|0.00048|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1021) ORDER BY addtime DESC,id DESC LIMIT 0,999
1|=phpok=|0.00041|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1021)
1|=phpok=|0.00037|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1013) ORDER BY addtime DESC,id DESC LIMIT 0,999
1|=phpok=|0.00036|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1013)
1|=phpok=|0.00027|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1018) ORDER BY addtime DESC,id DESC LIMIT 0,999
1|=phpok=|0.00056|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1018)
1|=phpok=|0.00091|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id IN('1763','1760','1762','1753','1761') AND site_id='1' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.0006|=phpok=|SELECT * FROM qinggan_project WHERE id='147' AND site_id='1'
1|=phpok=|0.00053|=phpok=|SELECT e.*,c.content content_val FROM qinggan_fields e LEFT JOIN qinggan_extc c ON(e.id=c.id) WHERE e.ftype='project-147' ORDER BY e.taxis asc,id DESC
1|=phpok=|0.00064|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='p147' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00028|=phpok=|SELECT parent_id FROM qinggan_project WHERE id='147' AND status=1
1|=phpok=|0.00039|=phpok=|SELECT count(l.id) FROM qinggan_list l  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=147  AND l.status=1  AND l.parent_id=0
1|=phpok=|0.00059|=phpok=|SELECT l.*,ext.link,ext.target FROM qinggan_list l  JOIN qinggan_list_23 ext  ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id)  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=147  AND l.status=1  AND l.parent_id=0   ORDER BY l.sort ASC,l.dateline DESC,l.id DESC  LIMIT 0,10
1|=phpok=|0.0028|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype IN('list-1300','list-1301','list-1302','list-1303','list-1304','list-1932') ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.0009|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id IN('1300','1301','1302','1303','1304','1932') AND site_id='1' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00046|=phpok=|SELECT * FROM qinggan_project WHERE id='148' AND site_id='1'
1|=phpok=|0.00045|=phpok=|SELECT e.*,c.content content_val FROM qinggan_fields e LEFT JOIN qinggan_extc c ON(e.id=c.id) WHERE e.ftype='project-148' ORDER BY e.taxis asc,id DESC
1|=phpok=|0.00048|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='p148' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.0003|=phpok=|SELECT parent_id FROM qinggan_project WHERE id='148' AND status=1
1|=phpok=|0.00063|=phpok=|SELECT * FROM qinggan_fields WHERE ftype='64' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00048|=phpok=|SELECT count(l.id) FROM qinggan_list l  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=148  AND l.status=1  AND l.parent_id=0
1|=phpok=|0.00095|=phpok=|SELECT l.*,ext.qtype,ext.qq,ext.weixin,ext.qrcode FROM qinggan_list l  JOIN qinggan_list_64 ext  ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id)  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=148  AND l.status=1  AND l.parent_id=0   ORDER BY l.sort ASC,l.dateline DESC,l.id DESC  LIMIT 0,50
1|=phpok=|0.00295|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype IN('list-1427','list-1305') ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00039|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1348) ORDER BY addtime DESC,id DESC LIMIT 0,999
1|=phpok=|0.00041|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1348)
1|=phpok=|0.00047|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id IN('1427','1305') AND site_id='1' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00028|=phpok=|SELECT * FROM qinggan_tpl WHERE id='1'
1|=phpok=|0.00079|=phpok=|SELECT * FROM qinggan_plugins WHERE status=1  ORDER BY taxis ASC,id DESC
1|=phpok=|0.00037|=phpok=|SELECT * FROM qinggan_plugins WHERE id='sitecopy'
1|=phpok=|0.00035|=phpok=|SELECT * FROM qinggan_plugins WHERE id='identifier'
1|=phpok=|0.00035|=phpok=|SELECT id FROM qinggan_cart WHERE session_id='2alfhatmorcku05oioi627o875'
1|=phpok=|0.00029|=phpok=|UPDATE qinggan_cart SET `session_id`='2alfhatmorcku05oioi627o875',`user_id`='',`addtime`='1556477156' WHERE `id`='8'
1|=phpok=|0.00019|=phpok=|SELECT SUM(qty) FROM qinggan_cart_product WHERE cart_id='8'
1|=phpok=|0.00059|=phpok=|SELECT * FROM qinggan_tpl WHERE id='1'
1|=phpok=|0.00042|=phpok=|SELECT * FROM qinggan_plugins WHERE status=1  ORDER BY taxis ASC,id DESC
1|=phpok=|0.00048|=phpok=|SELECT * FROM qinggan_plugins WHERE id='sitecopy'
1|=phpok=|0.00039|=phpok=|SELECT * FROM qinggan_plugins WHERE id='identifier'
1|=phpok=|0.00032|=phpok=|UPDATE qinggan_task SET is_lock=0 WHERE exec_time<1556477152 AND exec_time>0 AND is_lock=1
1|=phpok=|0.00028|=phpok=|SELECT * FROM qinggan_task WHERE 1=1 ORDER BY id ASC
1|=phpok=|0.00031|=phpok=|SELECT * FROM qinggan_tag WHERE title='公司' AND site_id='1'
1|=phpok=|0.00168|=phpok=|UPDATE qinggan_tag SET hits=hits+1 WHERE id='3'
1|=phpok=|0.00038|=phpok=|SELECT count(s.title_id) FROM qinggan_tag_stat s JOIN qinggan_list l ON(s.title_id=l.id) WHERE l.status=1 AND s.tag_id='3'
1|=phpok=|0.00036|=phpok=|SELECT title_id as id FROM qinggan_tag_stat WHERE tag_id='3'  ORDER BY title_id DESC LIMIT 0,20
1|=phpok=|0.00047|=phpok=|SELECT * FROM qinggan_cate WHERE `id`='8'
3|=phpok=|0.00106|=phpok=|SELECT * FROM qinggan_cate WHERE `id`='7'
3|=phpok=|0.00146|=phpok=|SELECT e.*,c.content content_val FROM qinggan_fields e LEFT JOIN qinggan_extc c ON(e.id=c.id) WHERE e.ftype='cate-7' ORDER BY e.taxis asc,id DESC
1|=phpok=|0.00044|=phpok=|SELECT * FROM qinggan_fields WHERE ftype='81' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00023|=phpok=|SELECT demo FROM qinggan_cate_81 WHERE id='8'
1|=phpok=|0.00038|=phpok=|SELECT e.*,c.content content_val FROM qinggan_fields e LEFT JOIN qinggan_extc c ON(e.id=c.id) WHERE e.ftype='cate-8' ORDER BY e.taxis asc,id DESC
1|=phpok=|0.00043|=phpok=|SELECT * FROM qinggan_project WHERE 1=1  AND cate='7' AND status=1
1|=phpok=|0.00036|=phpok=|SELECT * FROM qinggan_cate WHERE `id`='628'
2|=phpok=|0.0011|=phpok=|SELECT * FROM qinggan_project WHERE 1=1  AND cate='' AND status=1
1|=phpok=|0.00103|=phpok=|SELECT * FROM qinggan_cate WHERE site_id='1' AND status=1 ORDER BY taxis ASC,id DESC
1|=phpok=|0.00045|=phpok=|SELECT e.*,c.content content_val FROM qinggan_fields e LEFT JOIN qinggan_extc c ON(e.id=c.id) WHERE e.ftype='cate-%' ORDER BY e.taxis asc,id DESC
1|=phpok=|0.00033|=phpok=|SELECT * FROM qinggan_cate WHERE `id`='598'
1|=phpok=|0.00035|=phpok=|SELECT * FROM qinggan_list WHERE site_id='1' AND  id='1996'  AND status=1
1|=phpok=|0.00033|=phpok=|SELECT * FROM qinggan_list_biz WHERE id='1996'
1|=phpok=|0.00045|=phpok=|SELECT * FROM qinggan_list_22 WHERE id='1996'
1|=phpok=|0.00034|=phpok=|SELECT * FROM qinggan_list_attr WHERE tid='1996' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00362|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype='list-1996' ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00044|=phpok=|SELECT * FROM qinggan_project WHERE id='43' AND site_id='1'
1|=phpok=|0.00054|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='1996' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.0004|=phpok=|SELECT * FROM qinggan_fields WHERE ftype='22' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00079|=phpok=|SELECT c.* FROM qinggan_list_cate lc JOIN qinggan_cate c ON(lc.cate_id=c.id) WHERE lc.id='1996' AND c.status=1 ORDER BY c.taxis ASC,c.id DESC
1|=phpok=|0.00284|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype IN('cate-68') ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00046|=phpok=|SELECT * FROM qinggan_list WHERE site_id='1' AND  id='1763'  AND status=1
1|=phpok=|0.0004|=phpok=|SELECT * FROM qinggan_list_biz WHERE id='1763'
1|=phpok=|0.00041|=phpok=|SELECT * FROM qinggan_list_24 WHERE id='1763'
1|=phpok=|0.00044|=phpok=|SELECT * FROM qinggan_list_attr WHERE tid='1763' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00037|=phpok=|SELECT * FROM qinggan_attr WHERE site_id='1' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00041|=phpok=|SELECT * FROM qinggan_attr_values  WHERE id IN(34,1,35,3,4) ORDER BY taxis ASC,id DESC LIMIT 0,999
1|=phpok=|0.003|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype='list-1763' ORDER BY ext.taxis ASC,ext.id DESC
3|=phpok=|0.00159|=phpok=|SELECT * FROM qinggan_project WHERE id='45' AND site_id='1'
1|=phpok=|0.00058|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='1763' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00049|=phpok=|SELECT * FROM qinggan_fields WHERE ftype='24' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00041|=phpok=|SELECT * FROM qinggan_gd ORDER BY id DESC
2|=phpok=|0.00074|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1027) ORDER BY addtime DESC,id DESC LIMIT 0,999
2|=phpok=|0.00074|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1027)
1|=phpok=|0.00045|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1025,1026,1027,1028) ORDER BY addtime DESC,id DESC LIMIT 0,999
1|=phpok=|0.00041|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1028,1027,1026,1025)
1|=phpok=|0.00052|=phpok=|SELECT c.* FROM qinggan_list_cate lc JOIN qinggan_cate c ON(lc.cate_id=c.id) WHERE lc.id='1763' AND c.status=1 ORDER BY c.taxis ASC,c.id DESC
1|=phpok=|0.00313|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype IN('cate-216') ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00041|=phpok=|SELECT * FROM qinggan_site
1|=phpok=|0.00073|=phpok=|SELECT * FROM qinggan_phpok WHERE site_id='1' AND status=1
1|=phpok=|0.0006|=phpok=|SELECT * FROM qinggan_project WHERE id='42' AND site_id='1'
1|=phpok=|0.00039|=phpok=|SELECT e.*,c.content content_val FROM qinggan_fields e LEFT JOIN qinggan_extc c ON(e.id=c.id) WHERE e.ftype='project-42' ORDER BY e.taxis asc,id DESC
2|=phpok=|0.00089|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='p42' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.0003|=phpok=|SELECT parent_id FROM qinggan_project WHERE id='42' AND status=1
1|=phpok=|0.00056|=phpok=|SELECT * FROM qinggan_fields WHERE ftype='23' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00063|=phpok=|SELECT count(l.id) FROM qinggan_list l  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=42  AND l.status=1
1|=phpok=|0.00107|=phpok=|SELECT l.*,ext.link,ext.target FROM qinggan_list l  JOIN qinggan_list_23 ext  ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id)  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=42  AND l.status=1   ORDER BY l.sort ASC,l.dateline DESC,l.id DESC  LIMIT 0,80
1|=phpok=|0.0037|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype IN('list-760','list-520','list-761','list-712','list-755','list-1254','list-713','list-714','list-716','list-1256','list-1298','list-1299') ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.0008|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id IN('760','520','761','712','755','1254','713','714','716','1256','1298','1299') AND site_id='1' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00048|=phpok=|SELECT * FROM qinggan_list WHERE site_id='1' AND  identifier='contactus'  AND status=1
1|=phpok=|0.0003|=phpok=|SELECT * FROM qinggan_list_biz WHERE id='1757'
1|=phpok=|0.00032|=phpok=|SELECT * FROM qinggan_list_40 WHERE id='1757'
1|=phpok=|0.00039|=phpok=|SELECT * FROM qinggan_list_attr WHERE tid='1757' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00373|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype='list-1757' ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00059|=phpok=|SELECT * FROM qinggan_project WHERE id='87' AND site_id='1'
1|=phpok=|0.00051|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='1757' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00037|=phpok=|SELECT parent_id,cate_id,module_id,project_id,site_id FROM qinggan_list WHERE id='1757' AND status=1
1|=phpok=|0.00043|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='p87' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00028|=phpok=|SELECT parent_id FROM qinggan_project WHERE id='87' AND status=1
1|=phpok=|0.00086|=phpok=|SELECT * FROM qinggan_fields WHERE ftype='40' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00058|=phpok=|SELECT c.* FROM qinggan_list_cate lc JOIN qinggan_cate c ON(lc.cate_id=c.id) WHERE lc.id='1757' AND c.status=1 ORDER BY c.taxis ASC,c.id DESC
1|=phpok=|0.00067|=phpok=|SELECT e.*,c.content content_val FROM qinggan_fields e LEFT JOIN qinggan_extc c ON(e.id=c.id) WHERE e.ftype='project-45' ORDER BY e.taxis asc,id DESC
1|=phpok=|0.00052|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='p45' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.0003|=phpok=|SELECT parent_id FROM qinggan_project WHERE id='45' AND status=1
1|=phpok=|0.00043|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1007) ORDER BY addtime DESC,id DESC LIMIT 0,999
1|=phpok=|0.00045|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1007)
1|=phpok=|0.00362|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id) WHERE ext.ftype LIKE 'cate%' ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00407|=phpok=|SHOW COLUMNS FROM qinggan_list
1|=phpok=|0.00066|=phpok=|SELECT count(l.id) FROM qinggan_list l  JOIN qinggan_list_24 ext  ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id)  LEFT JOIN qinggan_list_cate lc ON(l.id=lc.id)  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=45  AND l.status=1  AND l.parent_id=0  AND lc.cate_id IN(70,168,582,583,584,585,216,180,219)  AND ext.thumb != ''
1|=phpok=|0.00112|=phpok=|SELECT DISTINCT l.id,l.parent_id,l.cate_id,l.module_id,l.project_id,l.site_id,l.title,l.dateline,l.sort,l.status,l.hidden,l.hits,l.tpl,l.seo_title,l.seo_keywords,l.seo_desc,l.tag,l.attr,l.replydate,l.user_id,l.identifier,l.integral,l.style,ext.thumb,b.price,b.currency_id,b.weight,b.volume,b.unit FROM qinggan_list l  JOIN qinggan_list_24 ext  ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id)  LEFT JOIN qinggan_list_biz b ON(b.id=l.id)  LEFT JOIN qinggan_list_cate lc ON(l.id=lc.id)  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=45  AND l.status=1  AND l.parent_id=0  AND lc.cate_id IN(70,168,582,583,584,585,216,180,219)  AND ext.thumb != ''  ORDER BY l.hits DESC  LIMIT 0,5
1|=phpok=|0.00047|=phpok=|SELECT lc.id,lc.cate_id,c.title,c.identifier FROM qinggan_list_cate lc LEFT JOIN qinggan_cate c ON(lc.cate_id=c.id) WHERE lc.id IN(1763,1760,1762,1753,1761)
1|=phpok=|0.00336|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype IN('list-1763','list-1760','list-1762','list-1753','list-1761') ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00039|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1015) ORDER BY addtime DESC,id DESC LIMIT 0,999
1|=phpok=|0.00039|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1015)
1|=phpok=|0.00034|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1021) ORDER BY addtime DESC,id DESC LIMIT 0,999
1|=phpok=|0.0004|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1021)
1|=phpok=|0.00036|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1013) ORDER BY addtime DESC,id DESC LIMIT 0,999
1|=phpok=|0.00035|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1013)
1|=phpok=|0.00047|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1018) ORDER BY addtime DESC,id DESC LIMIT 0,999
1|=phpok=|0.00051|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1018)
1|=phpok=|0.00118|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id IN('1763','1760','1762','1753','1761') AND site_id='1' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00056|=phpok=|SELECT * FROM qinggan_project WHERE id='147' AND site_id='1'
1|=phpok=|0.0006|=phpok=|SELECT e.*,c.content content_val FROM qinggan_fields e LEFT JOIN qinggan_extc c ON(e.id=c.id) WHERE e.ftype='project-147' ORDER BY e.taxis asc,id DESC
1|=phpok=|0.00049|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='p147' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00028|=phpok=|SELECT parent_id FROM qinggan_project WHERE id='147' AND status=1
1|=phpok=|0.00034|=phpok=|SELECT count(l.id) FROM qinggan_list l  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=147  AND l.status=1  AND l.parent_id=0
1|=phpok=|0.00063|=phpok=|SELECT l.*,ext.link,ext.target FROM qinggan_list l  JOIN qinggan_list_23 ext  ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id)  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=147  AND l.status=1  AND l.parent_id=0   ORDER BY l.sort ASC,l.dateline DESC,l.id DESC  LIMIT 0,10
1|=phpok=|0.00322|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype IN('list-1300','list-1301','list-1302','list-1303','list-1304','list-1932') ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00091|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id IN('1300','1301','1302','1303','1304','1932') AND site_id='1' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00063|=phpok=|SELECT * FROM qinggan_project WHERE id='148' AND site_id='1'
1|=phpok=|0.00063|=phpok=|SELECT e.*,c.content content_val FROM qinggan_fields e LEFT JOIN qinggan_extc c ON(e.id=c.id) WHERE e.ftype='project-148' ORDER BY e.taxis asc,id DESC
1|=phpok=|0.00051|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='p148' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00031|=phpok=|SELECT parent_id FROM qinggan_project WHERE id='148' AND status=1
1|=phpok=|0.00061|=phpok=|SELECT * FROM qinggan_fields WHERE ftype='64' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00037|=phpok=|SELECT count(l.id) FROM qinggan_list l  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=148  AND l.status=1  AND l.parent_id=0
1|=phpok=|0.00062|=phpok=|SELECT l.*,ext.qtype,ext.qq,ext.weixin,ext.qrcode FROM qinggan_list l  JOIN qinggan_list_64 ext  ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id)  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=148  AND l.status=1  AND l.parent_id=0   ORDER BY l.sort ASC,l.dateline DESC,l.id DESC  LIMIT 0,50
1|=phpok=|0.00279|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype IN('list-1427','list-1305') ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00035|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1348) ORDER BY addtime DESC,id DESC LIMIT 0,999
1|=phpok=|0.00038|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1348)
1|=phpok=|0.00054|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id IN('1427','1305') AND site_id='1' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00035|=phpok=|SELECT * FROM qinggan_tpl WHERE id='1'
1|=phpok=|0.00047|=phpok=|SELECT * FROM qinggan_plugins WHERE status=1  ORDER BY taxis ASC,id DESC
1|=phpok=|0.00038|=phpok=|SELECT * FROM qinggan_plugins WHERE id='sitecopy'
1|=phpok=|0.00036|=phpok=|SELECT * FROM qinggan_plugins WHERE id='identifier'
1|=phpok=|0.00033|=phpok=|SELECT id FROM qinggan_cart WHERE session_id='2alfhatmorcku05oioi627o875'
1|=phpok=|0.00036|=phpok=|UPDATE qinggan_cart SET `session_id`='2alfhatmorcku05oioi627o875',`user_id`='',`addtime`='1556477183' WHERE `id`='8'
1|=phpok=|0.00024|=phpok=|SELECT SUM(qty) FROM qinggan_cart_product WHERE cart_id='8'
1|=phpok=|0.00044|=phpok=|SELECT * FROM qinggan_tpl WHERE id='1'
1|=phpok=|0.00069|=phpok=|SELECT * FROM qinggan_plugins WHERE status=1  ORDER BY taxis ASC,id DESC
1|=phpok=|0.00044|=phpok=|SELECT * FROM qinggan_plugins WHERE id='sitecopy'
1|=phpok=|0.0005|=phpok=|SELECT * FROM qinggan_plugins WHERE id='identifier'
1|=phpok=|0.00043|=phpok=|UPDATE qinggan_task SET is_lock=0 WHERE exec_time<1556477179 AND exec_time>0 AND is_lock=1
1|=phpok=|0.00048|=phpok=|SELECT * FROM qinggan_task WHERE 1=1 ORDER BY id ASC
1|=phpok=|0.00043|=phpok=|SELECT * FROM qinggan_tag WHERE title='公司' AND site_id='1'
1|=phpok=|0.0019|=phpok=|UPDATE qinggan_tag SET hits=hits+1 WHERE id='3'
1|=phpok=|0.00052|=phpok=|SELECT count(s.title_id) FROM qinggan_tag_stat s JOIN qinggan_list l ON(s.title_id=l.id) WHERE l.status=1 AND s.tag_id='3'
1|=phpok=|0.0004|=phpok=|SELECT title_id as id FROM qinggan_tag_stat WHERE tag_id='3'  ORDER BY title_id DESC LIMIT 0,20
1|=phpok=|0.00041|=phpok=|SELECT * FROM qinggan_cate WHERE `id`='8'
3|=phpok=|0.00118|=phpok=|SELECT * FROM qinggan_cate WHERE `id`='7'
3|=phpok=|0.00137|=phpok=|SELECT e.*,c.content content_val FROM qinggan_fields e LEFT JOIN qinggan_extc c ON(e.id=c.id) WHERE e.ftype='cate-7' ORDER BY e.taxis asc,id DESC
1|=phpok=|0.0007|=phpok=|SELECT * FROM qinggan_fields WHERE ftype='81' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00034|=phpok=|SELECT demo FROM qinggan_cate_81 WHERE id='8'
1|=phpok=|0.00041|=phpok=|SELECT e.*,c.content content_val FROM qinggan_fields e LEFT JOIN qinggan_extc c ON(e.id=c.id) WHERE e.ftype='cate-8' ORDER BY e.taxis asc,id DESC
1|=phpok=|0.00048|=phpok=|SELECT * FROM qinggan_project WHERE 1=1  AND cate='7' AND status=1
1|=phpok=|0.0004|=phpok=|SELECT * FROM qinggan_cate WHERE `id`='628'
2|=phpok=|0.00128|=phpok=|SELECT * FROM qinggan_project WHERE 1=1  AND cate='' AND status=1
1|=phpok=|0.00084|=phpok=|SELECT * FROM qinggan_cate WHERE site_id='1' AND status=1 ORDER BY taxis ASC,id DESC
1|=phpok=|0.00049|=phpok=|SELECT e.*,c.content content_val FROM qinggan_fields e LEFT JOIN qinggan_extc c ON(e.id=c.id) WHERE e.ftype='cate-%' ORDER BY e.taxis asc,id DESC
1|=phpok=|0.00028|=phpok=|SELECT * FROM qinggan_cate WHERE `id`='598'
1|=phpok=|0.0004|=phpok=|SELECT * FROM qinggan_list WHERE site_id='1' AND  id='1996'  AND status=1
1|=phpok=|0.00024|=phpok=|SELECT * FROM qinggan_list_biz WHERE id='1996'
1|=phpok=|0.00042|=phpok=|SELECT * FROM qinggan_list_22 WHERE id='1996'
1|=phpok=|0.00026|=phpok=|SELECT * FROM qinggan_list_attr WHERE tid='1996' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00287|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype='list-1996' ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00042|=phpok=|SELECT * FROM qinggan_project WHERE id='43' AND site_id='1'
1|=phpok=|0.00055|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='1996' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00052|=phpok=|SELECT * FROM qinggan_fields WHERE ftype='22' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00056|=phpok=|SELECT c.* FROM qinggan_list_cate lc JOIN qinggan_cate c ON(lc.cate_id=c.id) WHERE lc.id='1996' AND c.status=1 ORDER BY c.taxis ASC,c.id DESC
1|=phpok=|0.003|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype IN('cate-68') ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00046|=phpok=|SELECT * FROM qinggan_list WHERE site_id='1' AND  id='1763'  AND status=1
1|=phpok=|0.00031|=phpok=|SELECT * FROM qinggan_list_biz WHERE id='1763'
1|=phpok=|0.00036|=phpok=|SELECT * FROM qinggan_list_24 WHERE id='1763'
1|=phpok=|0.00039|=phpok=|SELECT * FROM qinggan_list_attr WHERE tid='1763' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00032|=phpok=|SELECT * FROM qinggan_attr WHERE site_id='1' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00036|=phpok=|SELECT * FROM qinggan_attr_values  WHERE id IN(34,1,35,3,4) ORDER BY taxis ASC,id DESC LIMIT 0,999
1|=phpok=|0.00361|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype='list-1763' ORDER BY ext.taxis ASC,ext.id DESC
3|=phpok=|0.00143|=phpok=|SELECT * FROM qinggan_project WHERE id='45' AND site_id='1'
1|=phpok=|0.00065|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='1763' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.0004|=phpok=|SELECT * FROM qinggan_fields WHERE ftype='24' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00036|=phpok=|SELECT * FROM qinggan_gd ORDER BY id DESC
2|=phpok=|0.00076|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1027) ORDER BY addtime DESC,id DESC LIMIT 0,999
2|=phpok=|0.00085|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1027)
1|=phpok=|0.00058|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1025,1026,1027,1028) ORDER BY addtime DESC,id DESC LIMIT 0,999
1|=phpok=|0.00048|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1028,1027,1026,1025)
1|=phpok=|0.00052|=phpok=|SELECT c.* FROM qinggan_list_cate lc JOIN qinggan_cate c ON(lc.cate_id=c.id) WHERE lc.id='1763' AND c.status=1 ORDER BY c.taxis ASC,c.id DESC
1|=phpok=|0.00272|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype IN('cate-216') ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00049|=phpok=|SELECT * FROM qinggan_site
1|=phpok=|0.00057|=phpok=|SELECT * FROM qinggan_phpok WHERE site_id='1' AND status=1
1|=phpok=|0.00041|=phpok=|SELECT * FROM qinggan_project WHERE id='42' AND site_id='1'
1|=phpok=|0.00038|=phpok=|SELECT e.*,c.content content_val FROM qinggan_fields e LEFT JOIN qinggan_extc c ON(e.id=c.id) WHERE e.ftype='project-42' ORDER BY e.taxis asc,id DESC
2|=phpok=|0.00114|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='p42' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00047|=phpok=|SELECT parent_id FROM qinggan_project WHERE id='42' AND status=1
1|=phpok=|0.00055|=phpok=|SELECT * FROM qinggan_fields WHERE ftype='23' ORDER BY taxis ASC,id DESC
1|=phpok=|0.0004|=phpok=|SELECT count(l.id) FROM qinggan_list l  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=42  AND l.status=1
1|=phpok=|0.00083|=phpok=|SELECT l.*,ext.link,ext.target FROM qinggan_list l  JOIN qinggan_list_23 ext  ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id)  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=42  AND l.status=1   ORDER BY l.sort ASC,l.dateline DESC,l.id DESC  LIMIT 0,80
1|=phpok=|0.00343|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype IN('list-760','list-520','list-761','list-712','list-755','list-1254','list-713','list-714','list-716','list-1256','list-1298','list-1299') ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00058|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id IN('760','520','761','712','755','1254','713','714','716','1256','1298','1299') AND site_id='1' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00048|=phpok=|SELECT * FROM qinggan_list WHERE site_id='1' AND  identifier='contactus'  AND status=1
1|=phpok=|0.00032|=phpok=|SELECT * FROM qinggan_list_biz WHERE id='1757'
1|=phpok=|0.00037|=phpok=|SELECT * FROM qinggan_list_40 WHERE id='1757'
1|=phpok=|0.00034|=phpok=|SELECT * FROM qinggan_list_attr WHERE tid='1757' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00336|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype='list-1757' ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00045|=phpok=|SELECT * FROM qinggan_project WHERE id='87' AND site_id='1'
1|=phpok=|0.00042|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='1757' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00031|=phpok=|SELECT parent_id,cate_id,module_id,project_id,site_id FROM qinggan_list WHERE id='1757' AND status=1
1|=phpok=|0.00058|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='p87' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00043|=phpok=|SELECT parent_id FROM qinggan_project WHERE id='87' AND status=1
1|=phpok=|0.00041|=phpok=|SELECT * FROM qinggan_fields WHERE ftype='40' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00044|=phpok=|SELECT c.* FROM qinggan_list_cate lc JOIN qinggan_cate c ON(lc.cate_id=c.id) WHERE lc.id='1757' AND c.status=1 ORDER BY c.taxis ASC,c.id DESC
1|=phpok=|0.0005|=phpok=|SELECT e.*,c.content content_val FROM qinggan_fields e LEFT JOIN qinggan_extc c ON(e.id=c.id) WHERE e.ftype='project-45' ORDER BY e.taxis asc,id DESC
1|=phpok=|0.00065|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='p45' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00033|=phpok=|SELECT parent_id FROM qinggan_project WHERE id='45' AND status=1
1|=phpok=|0.0005|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1007) ORDER BY addtime DESC,id DESC LIMIT 0,999
1|=phpok=|0.00046|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1007)
1|=phpok=|0.00336|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id) WHERE ext.ftype LIKE 'cate%' ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00372|=phpok=|SHOW COLUMNS FROM qinggan_list
1|=phpok=|0.00052|=phpok=|SELECT count(l.id) FROM qinggan_list l  JOIN qinggan_list_24 ext  ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id)  LEFT JOIN qinggan_list_cate lc ON(l.id=lc.id)  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=45  AND l.status=1  AND l.parent_id=0  AND lc.cate_id IN(70,168,582,583,584,585,216,180,219)  AND ext.thumb != ''
1|=phpok=|0.00085|=phpok=|SELECT DISTINCT l.id,l.parent_id,l.cate_id,l.module_id,l.project_id,l.site_id,l.title,l.dateline,l.sort,l.status,l.hidden,l.hits,l.tpl,l.seo_title,l.seo_keywords,l.seo_desc,l.tag,l.attr,l.replydate,l.user_id,l.identifier,l.integral,l.style,ext.thumb,b.price,b.currency_id,b.weight,b.volume,b.unit FROM qinggan_list l  JOIN qinggan_list_24 ext  ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id)  LEFT JOIN qinggan_list_biz b ON(b.id=l.id)  LEFT JOIN qinggan_list_cate lc ON(l.id=lc.id)  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=45  AND l.status=1  AND l.parent_id=0  AND lc.cate_id IN(70,168,582,583,584,585,216,180,219)  AND ext.thumb != ''  ORDER BY l.hits DESC  LIMIT 0,5
1|=phpok=|0.00038|=phpok=|SELECT lc.id,lc.cate_id,c.title,c.identifier FROM qinggan_list_cate lc LEFT JOIN qinggan_cate c ON(lc.cate_id=c.id) WHERE lc.id IN(1763,1760,1762,1753,1761)
1|=phpok=|0.00368|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype IN('list-1763','list-1760','list-1762','list-1753','list-1761') ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00053|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1015) ORDER BY addtime DESC,id DESC LIMIT 0,999
1|=phpok=|0.00045|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1015)
1|=phpok=|0.00034|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1021) ORDER BY addtime DESC,id DESC LIMIT 0,999
1|=phpok=|0.00035|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1021)
1|=phpok=|0.00032|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1013) ORDER BY addtime DESC,id DESC LIMIT 0,999
1|=phpok=|0.00042|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1013)
1|=phpok=|0.00042|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1018) ORDER BY addtime DESC,id DESC LIMIT 0,999
1|=phpok=|0.00038|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1018)
1|=phpok=|0.0008|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id IN('1763','1760','1762','1753','1761') AND site_id='1' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00048|=phpok=|SELECT * FROM qinggan_project WHERE id='147' AND site_id='1'
1|=phpok=|0.00064|=phpok=|SELECT e.*,c.content content_val FROM qinggan_fields e LEFT JOIN qinggan_extc c ON(e.id=c.id) WHERE e.ftype='project-147' ORDER BY e.taxis asc,id DESC
1|=phpok=|0.00063|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='p147' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.0003|=phpok=|SELECT parent_id FROM qinggan_project WHERE id='147' AND status=1
1|=phpok=|0.0004|=phpok=|SELECT count(l.id) FROM qinggan_list l  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=147  AND l.status=1  AND l.parent_id=0
1|=phpok=|0.00064|=phpok=|SELECT l.*,ext.link,ext.target FROM qinggan_list l  JOIN qinggan_list_23 ext  ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id)  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=147  AND l.status=1  AND l.parent_id=0   ORDER BY l.sort ASC,l.dateline DESC,l.id DESC  LIMIT 0,10
1|=phpok=|0.00325|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype IN('list-1300','list-1301','list-1302','list-1303','list-1304','list-1932') ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00073|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id IN('1300','1301','1302','1303','1304','1932') AND site_id='1' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00046|=phpok=|SELECT * FROM qinggan_project WHERE id='148' AND site_id='1'
1|=phpok=|0.00037|=phpok=|SELECT e.*,c.content content_val FROM qinggan_fields e LEFT JOIN qinggan_extc c ON(e.id=c.id) WHERE e.ftype='project-148' ORDER BY e.taxis asc,id DESC
1|=phpok=|0.00034|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='p148' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.0002|=phpok=|SELECT parent_id FROM qinggan_project WHERE id='148' AND status=1
1|=phpok=|0.00041|=phpok=|SELECT * FROM qinggan_fields WHERE ftype='64' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00024|=phpok=|SELECT count(l.id) FROM qinggan_list l  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=148  AND l.status=1  AND l.parent_id=0
1|=phpok=|0.00075|=phpok=|SELECT l.*,ext.qtype,ext.qq,ext.weixin,ext.qrcode FROM qinggan_list l  JOIN qinggan_list_64 ext  ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id)  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=148  AND l.status=1  AND l.parent_id=0   ORDER BY l.sort ASC,l.dateline DESC,l.id DESC  LIMIT 0,50
1|=phpok=|0.00308|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype IN('list-1427','list-1305') ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00033|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1348) ORDER BY addtime DESC,id DESC LIMIT 0,999
1|=phpok=|0.00031|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1348)
1|=phpok=|0.0004|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id IN('1427','1305') AND site_id='1' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00041|=phpok=|SELECT * FROM qinggan_tpl WHERE id='1'
1|=phpok=|0.00042|=phpok=|SELECT * FROM qinggan_plugins WHERE status=1  ORDER BY taxis ASC,id DESC
1|=phpok=|0.00033|=phpok=|SELECT * FROM qinggan_plugins WHERE id='sitecopy'
1|=phpok=|0.00035|=phpok=|SELECT * FROM qinggan_plugins WHERE id='identifier'
1|=phpok=|0.00029|=phpok=|SELECT id FROM qinggan_cart WHERE session_id='2alfhatmorcku05oioi627o875'
1|=phpok=|0.0003|=phpok=|UPDATE qinggan_cart SET `session_id`='2alfhatmorcku05oioi627o875',`user_id`='',`addtime`='1556477256' WHERE `id`='8'
1|=phpok=|0.00021|=phpok=|SELECT SUM(qty) FROM qinggan_cart_product WHERE cart_id='8'
1|=phpok=|0.00051|=phpok=|SELECT * FROM qinggan_tpl WHERE id='1'
1|=phpok=|0.00057|=phpok=|SELECT * FROM qinggan_plugins WHERE status=1  ORDER BY taxis ASC,id DESC
1|=phpok=|0.00043|=phpok=|SELECT * FROM qinggan_plugins WHERE id='sitecopy'
1|=phpok=|0.00033|=phpok=|SELECT * FROM qinggan_plugins WHERE id='identifier'
1|=phpok=|0.00029|=phpok=|UPDATE qinggan_task SET is_lock=0 WHERE exec_time<1556477251 AND exec_time>0 AND is_lock=1
1|=phpok=|0.00035|=phpok=|SELECT * FROM qinggan_task WHERE 1=1 ORDER BY id ASC
1|=phpok=|0.00037|=phpok=|SELECT * FROM qinggan_tag WHERE title='公司' AND site_id='1'
1|=phpok=|0.00164|=phpok=|UPDATE qinggan_tag SET hits=hits+1 WHERE id='3'
1|=phpok=|0.00039|=phpok=|SELECT count(s.title_id) FROM qinggan_tag_stat s JOIN qinggan_list l ON(s.title_id=l.id) WHERE l.status=1 AND s.tag_id='3'
1|=phpok=|0.00048|=phpok=|SELECT title_id as id FROM qinggan_tag_stat WHERE tag_id='3'  ORDER BY title_id DESC LIMIT 0,20
1|=phpok=|0.00046|=phpok=|SELECT * FROM qinggan_cate WHERE `id`='8'
3|=phpok=|0.00126|=phpok=|SELECT * FROM qinggan_cate WHERE `id`='7'
3|=phpok=|0.00134|=phpok=|SELECT e.*,c.content content_val FROM qinggan_fields e LEFT JOIN qinggan_extc c ON(e.id=c.id) WHERE e.ftype='cate-7' ORDER BY e.taxis asc,id DESC
1|=phpok=|0.00039|=phpok=|SELECT * FROM qinggan_fields WHERE ftype='81' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00017|=phpok=|SELECT demo FROM qinggan_cate_81 WHERE id='8'
1|=phpok=|0.00037|=phpok=|SELECT e.*,c.content content_val FROM qinggan_fields e LEFT JOIN qinggan_extc c ON(e.id=c.id) WHERE e.ftype='cate-8' ORDER BY e.taxis asc,id DESC
1|=phpok=|0.00054|=phpok=|SELECT * FROM qinggan_project WHERE 1=1  AND cate='7' AND status=1
1|=phpok=|0.00039|=phpok=|SELECT * FROM qinggan_cate WHERE `id`='628'
2|=phpok=|0.00121|=phpok=|SELECT * FROM qinggan_project WHERE 1=1  AND cate='' AND status=1
1|=phpok=|0.001|=phpok=|SELECT * FROM qinggan_cate WHERE site_id='1' AND status=1 ORDER BY taxis ASC,id DESC
1|=phpok=|0.00041|=phpok=|SELECT e.*,c.content content_val FROM qinggan_fields e LEFT JOIN qinggan_extc c ON(e.id=c.id) WHERE e.ftype='cate-%' ORDER BY e.taxis asc,id DESC
1|=phpok=|0.00022|=phpok=|SELECT * FROM qinggan_cate WHERE `id`='598'
1|=phpok=|0.00047|=phpok=|SELECT * FROM qinggan_list WHERE site_id='1' AND  id='1996'  AND status=1
1|=phpok=|0.00046|=phpok=|SELECT * FROM qinggan_list_biz WHERE id='1996'
1|=phpok=|0.00069|=phpok=|SELECT * FROM qinggan_list_22 WHERE id='1996'
1|=phpok=|0.00029|=phpok=|SELECT * FROM qinggan_list_attr WHERE tid='1996' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00295|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype='list-1996' ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00041|=phpok=|SELECT * FROM qinggan_project WHERE id='43' AND site_id='1'
1|=phpok=|0.0005|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='1996' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00043|=phpok=|SELECT * FROM qinggan_fields WHERE ftype='22' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00057|=phpok=|SELECT c.* FROM qinggan_list_cate lc JOIN qinggan_cate c ON(lc.cate_id=c.id) WHERE lc.id='1996' AND c.status=1 ORDER BY c.taxis ASC,c.id DESC
1|=phpok=|0.00291|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype IN('cate-68') ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00065|=phpok=|SELECT * FROM qinggan_list WHERE site_id='1' AND  id='1763'  AND status=1
1|=phpok=|0.00044|=phpok=|SELECT * FROM qinggan_list_biz WHERE id='1763'
1|=phpok=|0.00042|=phpok=|SELECT * FROM qinggan_list_24 WHERE id='1763'
1|=phpok=|0.00043|=phpok=|SELECT * FROM qinggan_list_attr WHERE tid='1763' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00032|=phpok=|SELECT * FROM qinggan_attr WHERE site_id='1' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00043|=phpok=|SELECT * FROM qinggan_attr_values  WHERE id IN(34,1,35,3,4) ORDER BY taxis ASC,id DESC LIMIT 0,999
1|=phpok=|0.00298|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype='list-1763' ORDER BY ext.taxis ASC,ext.id DESC
3|=phpok=|0.00142|=phpok=|SELECT * FROM qinggan_project WHERE id='45' AND site_id='1'
1|=phpok=|0.00054|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='1763' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00051|=phpok=|SELECT * FROM qinggan_fields WHERE ftype='24' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00036|=phpok=|SELECT * FROM qinggan_gd ORDER BY id DESC
2|=phpok=|0.00073|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1027) ORDER BY addtime DESC,id DESC LIMIT 0,999
2|=phpok=|0.0007|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1027)
1|=phpok=|0.00041|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1025,1026,1027,1028) ORDER BY addtime DESC,id DESC LIMIT 0,999
1|=phpok=|0.00038|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1028,1027,1026,1025)
1|=phpok=|0.0005|=phpok=|SELECT c.* FROM qinggan_list_cate lc JOIN qinggan_cate c ON(lc.cate_id=c.id) WHERE lc.id='1763' AND c.status=1 ORDER BY c.taxis ASC,c.id DESC
1|=phpok=|0.00356|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype IN('cate-216') ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00043|=phpok=|SELECT * FROM qinggan_site
1|=phpok=|0.00056|=phpok=|SELECT * FROM qinggan_phpok WHERE site_id='1' AND status=1
1|=phpok=|0.00049|=phpok=|SELECT * FROM qinggan_project WHERE id='42' AND site_id='1'
1|=phpok=|0.00043|=phpok=|SELECT e.*,c.content content_val FROM qinggan_fields e LEFT JOIN qinggan_extc c ON(e.id=c.id) WHERE e.ftype='project-42' ORDER BY e.taxis asc,id DESC
2|=phpok=|0.00085|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='p42' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00035|=phpok=|SELECT parent_id FROM qinggan_project WHERE id='42' AND status=1
1|=phpok=|0.00045|=phpok=|SELECT * FROM qinggan_fields WHERE ftype='23' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00045|=phpok=|SELECT count(l.id) FROM qinggan_list l  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=42  AND l.status=1
1|=phpok=|0.00078|=phpok=|SELECT l.*,ext.link,ext.target FROM qinggan_list l  JOIN qinggan_list_23 ext  ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id)  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=42  AND l.status=1   ORDER BY l.sort ASC,l.dateline DESC,l.id DESC  LIMIT 0,80
1|=phpok=|0.00305|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype IN('list-760','list-520','list-761','list-712','list-755','list-1254','list-713','list-714','list-716','list-1256','list-1298','list-1299') ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00054|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id IN('760','520','761','712','755','1254','713','714','716','1256','1298','1299') AND site_id='1' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00041|=phpok=|SELECT * FROM qinggan_list WHERE site_id='1' AND  identifier='contactus'  AND status=1
1|=phpok=|0.0003|=phpok=|SELECT * FROM qinggan_list_biz WHERE id='1757'
1|=phpok=|0.00039|=phpok=|SELECT * FROM qinggan_list_40 WHERE id='1757'
1|=phpok=|0.0004|=phpok=|SELECT * FROM qinggan_list_attr WHERE tid='1757' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00319|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype='list-1757' ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00045|=phpok=|SELECT * FROM qinggan_project WHERE id='87' AND site_id='1'
1|=phpok=|0.00042|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='1757' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00034|=phpok=|SELECT parent_id,cate_id,module_id,project_id,site_id FROM qinggan_list WHERE id='1757' AND status=1
1|=phpok=|0.00039|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='p87' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00036|=phpok=|SELECT parent_id FROM qinggan_project WHERE id='87' AND status=1
1|=phpok=|0.00047|=phpok=|SELECT * FROM qinggan_fields WHERE ftype='40' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00046|=phpok=|SELECT c.* FROM qinggan_list_cate lc JOIN qinggan_cate c ON(lc.cate_id=c.id) WHERE lc.id='1757' AND c.status=1 ORDER BY c.taxis ASC,c.id DESC
1|=phpok=|0.00046|=phpok=|SELECT e.*,c.content content_val FROM qinggan_fields e LEFT JOIN qinggan_extc c ON(e.id=c.id) WHERE e.ftype='project-45' ORDER BY e.taxis asc,id DESC
1|=phpok=|0.00044|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='p45' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00026|=phpok=|SELECT parent_id FROM qinggan_project WHERE id='45' AND status=1
1|=phpok=|0.00031|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1007) ORDER BY addtime DESC,id DESC LIMIT 0,999
1|=phpok=|0.00036|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1007)
1|=phpok=|0.00337|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id) WHERE ext.ftype LIKE 'cate%' ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00373|=phpok=|SHOW COLUMNS FROM qinggan_list
1|=phpok=|0.0006|=phpok=|SELECT count(l.id) FROM qinggan_list l  JOIN qinggan_list_24 ext  ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id)  LEFT JOIN qinggan_list_cate lc ON(l.id=lc.id)  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=45  AND l.status=1  AND l.parent_id=0  AND lc.cate_id IN(70,168,582,583,584,585,216,180,219)  AND ext.thumb != ''
1|=phpok=|0.00098|=phpok=|SELECT DISTINCT l.id,l.parent_id,l.cate_id,l.module_id,l.project_id,l.site_id,l.title,l.dateline,l.sort,l.status,l.hidden,l.hits,l.tpl,l.seo_title,l.seo_keywords,l.seo_desc,l.tag,l.attr,l.replydate,l.user_id,l.identifier,l.integral,l.style,ext.thumb,b.price,b.currency_id,b.weight,b.volume,b.unit FROM qinggan_list l  JOIN qinggan_list_24 ext  ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id)  LEFT JOIN qinggan_list_biz b ON(b.id=l.id)  LEFT JOIN qinggan_list_cate lc ON(l.id=lc.id)  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=45  AND l.status=1  AND l.parent_id=0  AND lc.cate_id IN(70,168,582,583,584,585,216,180,219)  AND ext.thumb != ''  ORDER BY l.hits DESC  LIMIT 0,5
1|=phpok=|0.00036|=phpok=|SELECT lc.id,lc.cate_id,c.title,c.identifier FROM qinggan_list_cate lc LEFT JOIN qinggan_cate c ON(lc.cate_id=c.id) WHERE lc.id IN(1763,1760,1762,1753,1761)
1|=phpok=|0.00327|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype IN('list-1763','list-1760','list-1762','list-1753','list-1761') ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00034|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1015) ORDER BY addtime DESC,id DESC LIMIT 0,999
1|=phpok=|0.00035|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1015)
1|=phpok=|0.00045|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1021) ORDER BY addtime DESC,id DESC LIMIT 0,999
1|=phpok=|0.0004|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1021)
1|=phpok=|0.00032|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1013) ORDER BY addtime DESC,id DESC LIMIT 0,999
1|=phpok=|0.0004|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1013)
1|=phpok=|0.00031|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1018) ORDER BY addtime DESC,id DESC LIMIT 0,999
1|=phpok=|0.00033|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1018)
1|=phpok=|0.00081|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id IN('1763','1760','1762','1753','1761') AND site_id='1' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00049|=phpok=|SELECT * FROM qinggan_project WHERE id='147' AND site_id='1'
1|=phpok=|0.00036|=phpok=|SELECT e.*,c.content content_val FROM qinggan_fields e LEFT JOIN qinggan_extc c ON(e.id=c.id) WHERE e.ftype='project-147' ORDER BY e.taxis asc,id DESC
1|=phpok=|0.00036|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='p147' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.0002|=phpok=|SELECT parent_id FROM qinggan_project WHERE id='147' AND status=1
1|=phpok=|0.00035|=phpok=|SELECT count(l.id) FROM qinggan_list l  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=147  AND l.status=1  AND l.parent_id=0
1|=phpok=|0.00059|=phpok=|SELECT l.*,ext.link,ext.target FROM qinggan_list l  JOIN qinggan_list_23 ext  ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id)  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=147  AND l.status=1  AND l.parent_id=0   ORDER BY l.sort ASC,l.dateline DESC,l.id DESC  LIMIT 0,10
1|=phpok=|0.0029|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype IN('list-1300','list-1301','list-1302','list-1303','list-1304','list-1932') ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00085|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id IN('1300','1301','1302','1303','1304','1932') AND site_id='1' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00041|=phpok=|SELECT * FROM qinggan_project WHERE id='148' AND site_id='1'
1|=phpok=|0.0004|=phpok=|SELECT e.*,c.content content_val FROM qinggan_fields e LEFT JOIN qinggan_extc c ON(e.id=c.id) WHERE e.ftype='project-148' ORDER BY e.taxis asc,id DESC
1|=phpok=|0.00038|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='p148' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00023|=phpok=|SELECT parent_id FROM qinggan_project WHERE id='148' AND status=1
1|=phpok=|0.00044|=phpok=|SELECT * FROM qinggan_fields WHERE ftype='64' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00028|=phpok=|SELECT count(l.id) FROM qinggan_list l  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=148  AND l.status=1  AND l.parent_id=0
1|=phpok=|0.00069|=phpok=|SELECT l.*,ext.qtype,ext.qq,ext.weixin,ext.qrcode FROM qinggan_list l  JOIN qinggan_list_64 ext  ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id)  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=148  AND l.status=1  AND l.parent_id=0   ORDER BY l.sort ASC,l.dateline DESC,l.id DESC  LIMIT 0,50
1|=phpok=|0.00299|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype IN('list-1427','list-1305') ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00037|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1348) ORDER BY addtime DESC,id DESC LIMIT 0,999
1|=phpok=|0.00032|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1348)
1|=phpok=|0.00051|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id IN('1427','1305') AND site_id='1' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.0003|=phpok=|SELECT * FROM qinggan_tpl WHERE id='1'
1|=phpok=|0.00043|=phpok=|SELECT * FROM qinggan_plugins WHERE status=1  ORDER BY taxis ASC,id DESC
1|=phpok=|0.00039|=phpok=|SELECT * FROM qinggan_plugins WHERE id='sitecopy'
1|=phpok=|0.00049|=phpok=|SELECT * FROM qinggan_plugins WHERE id='identifier'
1|=phpok=|0.00035|=phpok=|SELECT id FROM qinggan_cart WHERE session_id='2alfhatmorcku05oioi627o875'
1|=phpok=|0.00035|=phpok=|UPDATE qinggan_cart SET `session_id`='2alfhatmorcku05oioi627o875',`user_id`='',`addtime`='1556477330' WHERE `id`='8'
1|=phpok=|0.00017|=phpok=|SELECT SUM(qty) FROM qinggan_cart_product WHERE cart_id='8'
1|=phpok=|0.00028|=phpok=|SELECT * FROM qinggan_tpl WHERE id='1'
1|=phpok=|0.00067|=phpok=|SELECT * FROM qinggan_plugins WHERE status=1  ORDER BY taxis ASC,id DESC
1|=phpok=|0.00041|=phpok=|SELECT * FROM qinggan_plugins WHERE id='sitecopy'
1|=phpok=|0.00036|=phpok=|SELECT * FROM qinggan_plugins WHERE id='identifier'
1|=phpok=|0.00054|=phpok=|UPDATE qinggan_task SET is_lock=0 WHERE exec_time<1556477326 AND exec_time>0 AND is_lock=1
1|=phpok=|0.00044|=phpok=|SELECT * FROM qinggan_task WHERE 1=1 ORDER BY id ASC
5|=phpok=|0.00275|=phpok=|SELECT * FROM qinggan_project WHERE id='43' AND site_id='1'
3|=phpok=|0.00175|=phpok=|SELECT e.*,c.content content_val FROM qinggan_fields e LEFT JOIN qinggan_extc c ON(e.id=c.id) WHERE e.ftype='project-43' ORDER BY e.taxis asc,id DESC
3|=phpok=|0.00153|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='p43' ORDER BY LENGTH(t.title) DESC
3|=phpok=|0.00109|=phpok=|SELECT parent_id FROM qinggan_project WHERE id='43' AND status=1
1|=phpok=|0.00078|=phpok=|SELECT * FROM qinggan_cate WHERE site_id='1' AND status=1 ORDER BY taxis ASC,id DESC
1|=phpok=|0.00048|=phpok=|SELECT e.*,c.content content_val FROM qinggan_fields e LEFT JOIN qinggan_extc c ON(e.id=c.id) WHERE e.ftype='cate-%' ORDER BY e.taxis asc,id DESC
2|=phpok=|0.00703|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id) WHERE ext.ftype LIKE 'cate%' ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00051|=phpok=|SELECT * FROM qinggan_fields WHERE ftype='22' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00038|=phpok=|SELECT count(l.id) FROM qinggan_list l  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=43  AND l.status=1  AND l.parent_id=0  AND l.cate_id IN(7,8,68)
1|=phpok=|0.0007|=phpok=|SELECT l.*,ext.thumb,ext.note FROM qinggan_list l  JOIN qinggan_list_22 ext  ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id)  WHERE  l.site_id='1' AND l.hidden=0  AND l.project_id=43  AND l.status=1  AND l.parent_id=0  AND l.cate_id IN(7,8,68)   ORDER BY l.id DESC  LIMIT 0,20
1|=phpok=|0.00055|=phpok=|SELECT * FROM qinggan_fields WHERE ftype='user'  ORDER BY taxis ASC,id DESC
1|=phpok=|0.00152|=phpok=|SELECT u.*,e.fullname,e.gender,e.pca,e.address FROM qinggan_user u  LEFT JOIN qinggan_user_ext e ON(u.id=e.id)  WHERE u.id IN(23) AND u.status=1 ORDER BY u.regtime DESC,u.id DESC
1|=phpok=|0.00096|=phpok=|SELECT * FROM qinggan_wealth WHERE site_id='1'  AND status='1'  ORDER BY taxis ASC
1|=phpok=|0.00072|=phpok=|SELECT wid,uid,val FROM qinggan_wealth_info WHERE uid IN(23)
1|=phpok=|0.00081|=phpok=|SELECT * FROM qinggan_opt_group WHERE id='19'
1|=phpok=|0.00172|=phpok=|SELECT * FROM qinggan_opt WHERE val='广东省' AND group_id='19' AND parent_id='0'
1|=phpok=|0.00131|=phpok=|SELECT * FROM qinggan_opt WHERE val='深圳市' AND group_id='19' AND parent_id='13382'
1|=phpok=|0.00114|=phpok=|SELECT * FROM qinggan_opt WHERE val='龙华区' AND group_id='19' AND parent_id='13397'
1|=phpok=|0.0004|=phpok=|SELECT lc.id,lc.cate_id,c.title,c.identifier FROM qinggan_list_cate lc LEFT JOIN qinggan_cate c ON(lc.cate_id=c.id) WHERE lc.id IN(1996,1936,1935,1934,1933)
1|=phpok=|0.00377|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype IN('list-1996','list-1936','list-1935','list-1934','list-1933') ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00045|=phpok=|SELECT * FROM qinggan_gd ORDER BY id DESC
1|=phpok=|0.00042|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1348) ORDER BY addtime DESC,id DESC LIMIT 0,999
1|=phpok=|0.00042|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1348)
1|=phpok=|0.00029|=phpok=|SELECT * FROM qinggan_res  WHERE id IN(1335) ORDER BY addtime DESC,id DESC LIMIT 0,999
1|=phpok=|0.00037|=phpok=|SELECT res.*,cate.etype FROM qinggan_res res LEFT JOIN qinggan_res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(1335)
1|=phpok=|0.00098|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id IN('1996','1936','1935','1934','1933') AND site_id='1' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00052|=phpok=|SELECT * FROM qinggan_site
1|=phpok=|0.00061|=phpok=|SELECT * FROM qinggan_phpok WHERE site_id='1' AND status=1
1|=phpok=|0.00045|=phpok=|SELECT * FROM qinggan_fields WHERE ftype='81' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00031|=phpok=|SELECT id,demo FROM qinggan_cate_81
1|=phpok=|0.00302|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype IN('') ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00047|=phpok=|SELECT * FROM qinggan_list WHERE site_id='1' AND  identifier='contactus'  AND status=1
1|=phpok=|0.00029|=phpok=|SELECT * FROM qinggan_list_biz WHERE id='1757'
1|=phpok=|0.0003|=phpok=|SELECT * FROM qinggan_list_40 WHERE id='1757'
1|=phpok=|0.00027|=phpok=|SELECT * FROM qinggan_list_attr WHERE tid='1757' ORDER BY taxis ASC,id DESC
1|=phpok=|0.00392|=phpok=|SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.ftype FROM qinggan_fields ext JOIN qinggan_extc extc ON(ext.id=extc.id)  WHERE ext.ftype='list-1757' ORDER BY ext.taxis ASC,ext.id DESC
1|=phpok=|0.00055|=phpok=|SELECT * FROM qinggan_project WHERE id='87' AND site_id='1'
1|=phpok=|0.0006|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='1757' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00028|=phpok=|SELECT parent_id,cate_id,module_id,project_id,site_id FROM qinggan_list WHERE id='1757' AND status=1
1|=phpok=|0.0005|=phpok=|SELECT t.*,s.title_id FROM qinggan_tag_stat s  JOIN qinggan_tag t ON(s.tag_id=t.id)  WHERE s.title_id='p87' ORDER BY LENGTH(t.title) DESC
1|=phpok=|0.00035|=phpok=|SELECT parent_id FROM qinggan_project WHERE id='87' AND status=1
1|=phpok=|0.00049|=phpok=|SELECT * FROM qinggan_fields WHERE ftype='40' ORDER BY taxis ASC,id DESC
1|=phpok=|0.0005|=phpok=|SELECT c.* FROM qinggan_list_cate lc JOIN qinggan_cate c ON(lc.cate_id=c.id) WHERE lc.id='1757' AND c.status=1 ORDER BY c.taxis ASC,c.id DESC
1|=phpok=|0.00051|=phpok=|SELECT * FROM qinggan_tpl WHERE id='1'
1|=phpok=|0.00047|=phpok=|SELECT * FROM qinggan_plugins WHERE status=1  ORDER BY taxis ASC,id DESC
1|=phpok=|0.00031|=phpok=|SELECT * FROM qinggan_plugins WHERE id='sitecopy'
1|=phpok=|0.00033|=phpok=|SELECT * FROM qinggan_plugins WHERE id='identifier'
1|=phpok=|0.00028|=phpok=|SELECT id FROM qinggan_cart WHERE session_id='2alfhatmorcku05oioi627o875'
1|=phpok=|0.00034|=phpok=|UPDATE qinggan_cart SET `session_id`='2alfhatmorcku05oioi627o875',`user_id`='',`addtime`='1556477339' WHERE `id`='8'
1|=phpok=|0.00026|=phpok=|SELECT SUM(qty) FROM qinggan_cart_product WHERE cart_id='8'
1|=phpok=|0.00052|=phpok=|SELECT * FROM qinggan_tpl WHERE id='1'
1|=phpok=|0.00045|=phpok=|SELECT * FROM qinggan_plugins WHERE status=1  ORDER BY taxis ASC,id DESC
1|=phpok=|0.00028|=phpok=|SELECT * FROM qinggan_plugins WHERE id='sitecopy'
1|=phpok=|0.00039|=phpok=|SELECT * FROM qinggan_plugins WHERE id='identifier'
1|=phpok=|0.00037|=phpok=|UPDATE qinggan_task SET is_lock=0 WHERE exec_time<1556477335 AND exec_time>0 AND is_lock=1
1|=phpok=|0.00053|=phpok=|SELECT * FROM qinggan_task WHERE 1=1 ORDER BY id ASC
