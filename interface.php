<?php
interface User{
	function existUserName();//$userName 判断一个用户名是否存在
	function add();//$data
	function del();//$uid
	function up();//$uid,$data
	function getByUid();//$uid获取一个用户
	function getByUids();//$uids获取多个用户
	function setRole();//$uid,$aid设置角色
}

interface Role{
	function existRoleName();//$roleName判断一个角色名是否存在
	function add();//$data
	function assign();//$aid,$rids分配权限
	function getActorId();//$uid,$name获取一个角色的ID
	function getActorInfo();//$aid根据角色ID获取角色信息
	function getActorAllInfo();//$uid根据用户ID获取所有角色信息
	function del();//$aid	
}

interface Resource{
	function add();//$data
	function isAccessByAc();//$uid,$ctrl,$ac
	function isAccessById();//$uid,$rid
	function getMenuResoByUid();//$uid获取菜单权限
	function getAcceResoByUid();//$uid获取用户访问权限
	function getUserReso();//$uid获取用户所有权限
	function del();//$rid
	function up();//$rid,$data
	function getByRid();//$rid
	function getByRids();//$fids
	function getResource();//获取所有资源列表
}

interface Menu{
	function add();//$data
	function del();//$mid
	function getTreeMenu();//获取所有菜单用于放置于SELECT框中
	function existMenuName();//$menuName判断一个角色名是否存在
	function up();//$mid,$data
	function menuLinkResource();//$mid,$rid资源关联菜单
	function getRootMenu();//$uid获取一级/顶级菜单
	function getLeftMenu();//$uid,$mid获取二级/左侧菜单
}
