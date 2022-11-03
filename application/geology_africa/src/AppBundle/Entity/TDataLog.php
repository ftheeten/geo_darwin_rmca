<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TDataLog
 *
 * @ORM\Table(name="t_data_log", indexes={@ORM\Index(name="IDX_1F52952AFF795F28", columns={"user_ref"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TDataLogRepository")
 */
class TDataLog
{
    /**
     * @var integer
     *
     * @ORM\Column(name="pk", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="t_data_log_pk_seq", allocationSize=1, initialValue=1)
     */
    private $pk;

    /**
     * @var string
     *
     * @ORM\Column(name="referenced_table", type="string", nullable=true)
     */
    private $referencedTable;

    /**
     * @var integer
     *
     * @ORM\Column(name="record_id", type="integer", nullable=true)
     */
    private $recordId;

    /**
     * @var string
     *
     * @ORM\Column(name="action", type="string", nullable=true)
     */
    private $action;

    /**
     * @var array
     *
     * @ORM\Column(name="old_value", type="json_array", nullable=true)
     */
    private $oldValue;

    /**
     * @var array
     *
     * @ORM\Column(name="new_value", type="json_array", nullable=true)
     */
    private $newValue;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modification_date_time", type="string", nullable=true)
     */
    private $modificationDateTime;// = 'now()';

    /**
     * @var string
     *
     * @ORM\Column(name="secondary_table", type="string", nullable=true)
     */
    private $secondaryTable;

    /**
     * @var \Duser
     *
     * @ORM\ManyToOne(targetEntity="Duser")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_ref", referencedColumnName="id")
     * })
     */
    private $userRef;
	
	public function setAction($action)
	{
		$this->action=$action;
	}
	
	public function setRecordId($record_id)
	{
		$this->recordId=$record_id;
	}
	
	public function setReferencedTable($table)
	{
		$this->referencedTable=$table;
	}
	
	public function setModificationDateTime($modification_date_time)
	{
		$this->modificationDateTime=$modification_date_time;
	}
	
	public function setUserRef($user_ref)
	{
		
		$this->userRef=$user_ref;
	}


}

