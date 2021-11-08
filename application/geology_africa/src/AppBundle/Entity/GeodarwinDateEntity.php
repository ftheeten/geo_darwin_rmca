<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/** GeodarwinDateEntity*/
class GeodarwinDateEntity
{


	protected function handle_date_general($year, $month=null, $day=null)
	{
		$returned=$year;
		if($month==null)
		{
			$returned.="-01";
		}
		else
		{
			$returned.="-".str_pad($month,2,0,STR_PAD_LEFT);
		}
		if($day==null)
		{
			$returned.="-01";
		}
		else
		{
			$returned.="-".str_pad($day,2,0,STR_PAD_LEFT);
		}
		return $returned;	
	}
	
	protected function trigger_date_error($form, $date, $format='Y-m-d')
	{
		$date_ctrl = DateTime::createFromFormat($format, $date);
		$date_errors = DateTime::getLastErrors();
		if($date_errors["warning_count"]>0 ||$date_errors["warning_count"]>0 )
		{
			$form->addError(new FormError("Invalid date".$date.". Check the date format"));
		}
	}
	
	protected function handle_date_format_general($year, $month=null, $day=null)
	{

		$returned=0;
		if($year!==null)
		{
			$returned=((int)$returned)+32;
		}
		if($month!==null)
		{
			$returned=((int)$returned)+16;
		}
		if($day!==null)
		{
			$returned=((int)$returned)+8;
		}

		return $returned;
	}
}