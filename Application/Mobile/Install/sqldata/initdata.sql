--
-- 转存表中的数据 `qs_menu`
--

INSERT INTO `qs_menu` (`id`, `name`, `pid`, `spid`, `log_cn`, `module_name`, `controller_name`, `action_name`, `menu_type`, `is_parent`, `data`, `remark`, `often`, `ordid`, `display`, `stat`, `sys_set`, `mods`) VALUES
(251, '移动端', 0, '251|', '修改移动端设置', 'Mobile', 'Admin', 'index', 1, 1, '', '', 0, 4, 1, '', 1, 0),
(403, '手机触屏端', 251, '251|403|', '', 'Mobile', 'Admin', 'index', 1, 0, '', '', 0, 255, 1, '', 0, 0);

--
-- 转存表中的数据 `qs_config`
--

INSERT INTO `qs_config` (`id`, `name`, `value`, `note`, `type`) VALUES
(NULL, 'wap_domain', '', '触屏版域名', 'Mobile'),
(NULL, 'mobile_captcha_open', '0', '是否开启触屏端极验', 'Mobile'),
(NULL, 'mobile_captcha', 'a:2:{s:2:"id";s:32:"7c25da6fe21944cfe507d2f9876775a9";s:3:"key";s:32:"f5883f4ee3bd4fa8caec67941de1b903";}', '验证账号触屏设置', 'Mobile'),
(NULL, 'mobile_isclose', '0', '触屏端是否关闭', 'Mobile'),
(NULL, 'mobile_close_reason', '测试中', '触屏站点关闭原因', 'Mobile'),
(NULL, 'mobile_statistics', '', '触屏端站点统计', 'Mobile'),
(NULL, 'mobile_closereg', '0', '触屏端关闭注册', 'Mobile'),
(NULL, 'resume_base', '10000', '简功数量基值', 'Mobile'),
(NULL, 'jobs_base', '10000', '职位数量基值', 'Mobile'),
(NULL, 'footer_nav_status', '0', '触屏端底部导航上否开启', 'Mobile'),
(NULL, 'wap_captcha_config', 'a:3:{s:10:"varify_reg";s:1:"1";s:13:"varify_mobile";s:1:"1";s:10:"user_login";s:1:"3";}', '触屏极验配置', 'Mobile');