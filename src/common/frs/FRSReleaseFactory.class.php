<?php

/**
 * Copyright (c) Xerox, 2006. All Rights Reserved.
 *
 * Originally written by Anne Hardyau, 2006
 *
 * This file is a part of CodeX.
 *
 * CodeX is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * CodeX is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with CodeX; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * $Id$
 */

require_once ('FRSRelease.class.php');
require_once ('common/dao/FRSReleaseDao.class.php');
require_once ('common/frs/FRSFileFactory.class.php');
/**
 * 
 */
class FRSReleaseFactory {

	function FRSReleaseFactory() {

	}

	function & getFRSReleaseFromArray(& $array) {
		$frs_release = null;
		$frs_release = new FRSRelease($array);
		return $frs_release;
	}

	function & getFRSReleaseFromDb($release_id, $group_id=null, $package_id=null) {
		$_id = (int) $release_id;
		$dao = & $this->_getFRSReleaseDao();
		if($group_id && $package_id){
			$_group_id = (int) $group_id;
			$_package_id = (int) $package_id;
			$dar = $dao->searchByGroupPackageReleaseID($_id, $_group_id, $package_id);
		}else if($group_id) {
			$_group_id = (int) $group_id;
			$dar = $dao->searchInGroupById($_id, $_group_id);
		}else{
			$dar = $dao->searchById($_id);
		}
		

		if ($dar->isError()) {
			return;
		}

		if (!$dar->valid()) {
			return;
		}

		$data_array = & $dar->current();

		return (FRSReleaseFactory :: getFRSReleaseFromArray($data_array));
	}

	function & getFRSReleasesFromDb($package_id) {
		$_id = (int) $package_id;
		$dao = & $this->_getFRSReleaseDao();
		$dar = $dao->searchByPackageId($_id);

		if ($dar->isError()) {
			return;
		}

		if (!$dar->valid()) {
			return;
		}

		$releases = array ();
		while ($dar->valid()) {
			$data_array = & $dar->current();
			$releases[] = FRSReleaseFactory :: getFRSReleaseFromArray($data_array);
			$dar->next();
		}

		return $releases;
	}
	
	function getFRSReleasesInfoListFromDb($group_id, $package_id=null) {
		$_id = (int) $group_id;
		$dao = & $this->_getFRSReleaseDao();
		if($package_id){
			$_package_id = (int) $package_id;
			$dar = $dao->searchByGroupPackageID($_id, $_package_id);
		}else{
			$dar = $dao->searchByGroupPackageID($_id);
		}

		if ($dar->isError()) {
			return;
		}

		if (!$dar->valid()) {
			return;
		}	

		$releases = array ();
		while ($dar->valid()) {
			$releases[] = $dar->current();
			$dar->next();
		}
		return $releases;
	}
	
	

	function isActiveReleases($package_id) {
		$_id = (int) $package_id;
		$dao = & $this->_getFRSReleaseDao();
		$dar = $dao->searchActiveReleasesByPackageId($_id);

		if ($dar->isError()) {
			return;
		}

		return $dar->valid();

	}
	
	function isReleaseNameExist($release_name, $package_id){
    	$_id = (int) $package_id;
        $dao =& $this->_getFRSReleaseDao();
        $dar = $dao->isReleaseNameExist($release_name, $_id);

        if($dar->isError()){
            return;
        }
        
        if(!$dar->valid()){
        	return;
        }else{
        	$res =& $dar->current();
        	return $res['release_id'];
        }
    }

	var $dao;

	function & _getFRSReleaseDao() {
		if (!$this->dao) {
			$this->dao = & new FRSReleaseDao(CodexDataAccess :: instance());
		}
		return $this->dao;
	}

	function update($data_array) {
		$dao = & $this->_getFRSReleaseDao();
		return $dao->updateFromArray($data_array);
	}

	function create($data_array) {
		$dao = & $this->_getFRSReleaseDao();
		$id = $dao->createFromArray($data_array);
		return $id;
	}
	
	function _delete($release_id){
    	$_id = (int) $release_id;
    	$dao =& $this->_getFRSReleaseDao();
    	return $dao->delete($_id);
    }

	/*
	
	Physically delete a release from the download server and database
	
	First, make sure the release is theirs
	Second, delete all its files from the db
	Third, delete the release itself from the deb
	Fourth, put it into the delete_files to be removed from the download server
	
	return 0 if release not deleted, 1 otherwise
	*/
	function delete_release($group_id, $release_id) {
		GLOBAL $ftp_incoming_dir;

		$release =& $this->getFRSReleaseFromDb($release_id, $group_id);
		
		if (!$release) {
			//release not found for this project
			return 0;
		} else {
			//delete all corresponding files from the database
			$res =& $release->getFiles();
			$rows = count($res);
			$frsff = new FRSFileFactory();
			for ($i = 0; $i < $rows; $i++) {
				$frsff->delete_file($group_id, $res[$i]->getFileID());
				$filename = $res[$i]->getFileName();
			}

			//delete the release from the database
			$this->_delete($release_id);

			//append the releasename and project name to a temp file for the root perl job to grab
			if ($filename) {
				//find the last occurrence of / in the filename to get the parentdir name
				$pos = strrpos($filename, "/");
				if (!$pos) {
					// not found...
				} else {
					$parentdir = substr($filename, 0, $pos);
					$time = time();
					exec("/bin/echo \"$parentdir::" . group_getunixname($group_id) . "::$time\" >> $ftp_incoming_dir/.delete_files");
				}
			}

			return 1;
		}
	}

}
?>
