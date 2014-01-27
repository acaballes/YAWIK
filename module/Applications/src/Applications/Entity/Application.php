<?php

namespace Applications\Entity;

use Core\Entity\AbstractIdentifiableEntity;
use Core\Entity\EntityInterface;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Auth\Entity\UserInterface;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Jobs\Entity\JobInterface;
use Doctrine\Common\Collections\Collection;

/**
 * The application model
 * 
 * @author mathias
 *
 * @ODM\Document(collection="applications", repositoryClass="Applications\Repository\Application")
 */
class Application extends AbstractIdentifiableEntity implements ApplicationInterface, ResourceInterface
{
    /**
     * @var unknown
     * @ODM\String
     */
    protected $jobId;
    
    /**
     * 
     * @var unknown
     * @ODM\ReferenceOne(targetDocument="Jobs\Entity\Job", simple=true, inversedBy="applications")
     */
    protected $job;
    
    /**
     * Owner of an application. If an authenticated Candidate submit an application, this
     * userId is set.
     * 
     * @var unknown
     * 
     * @ODM\String
     */
    protected $userId;
    
    /**
     * 
     * @var unknown
     */
    protected $user;
    
    /**
     * latest status of an application.
     * 
     * @var String 
     * 
     * @ODM\EmbedOne(targetDocument="Status")
     */
    protected $status;
    
    /**
     * Creation Date of an application
     * 
     * @var unknown
     * 
     * @ODM\Field(type="tz_date")
     */
    protected $dateCreated;
    
    /**
     * Latest modification date of an application
     * 
     * @var unknown
     * 
     * @ODM\Field(type="tz_date")
     */
    protected $dateModified;

    /**
     * personal informations, contains firstname, lastname, email, 
     * phone etc.
     *
     * @ODM\EmbedOne(targetDocument="Contact")
     */
    protected $contact;
    
    /**
     * The summary of an application
     * 
     * @var String
     * 
     * @ODM\String
     */
    protected $summary;
    
    /**
     * Resume, containing employments, educations and skills
     * 
     * @ODM\EmbedOne(targetDocument="Cv")
     */
    protected $cv;

    /**
     * multiple Attachments of an application
     * 
     * @ODM\ReferenceMany(targetDocument="Attachment", simple="true", cascade={"persist"})
     */
    protected $attachments;
    
    /**
     * 
     * @var unknown
     * @ODM\EmbedOne(targetDocument="History")
     */
    protected $history;
        
    /**
     * 
     * @var unknown
     */
    protected $privacyPolicy;
    
    /**
     * Who has read the application?.
     * 
     * @var unknown
     */
    protected $readBy = array();
    
    /**
     * 
     * @var 
     * @ODM\EmbedOne(targetDocument="InternalReferences")
     */
    protected $refs;
    
    public function getResourceId()
    {
        return 'Entity/Application';
    }
    
    /**
     * @return the $jobId
     */
    public function getJobId ()
    {
        if (!$this->jobId && ($job = $this->getJob())) {
            $this->setJobId($job->getId());
        }
        return $this->jobId;
    }

	/**
     * @param field_type $jobId
     */
    public function setJobId ($jobId)
    {
        $this->jobId = $jobId;
    }
    
    public function getJob()
    {
        return $this->job;
    }
    
    public function setJob(JobInterface $job)
    {
        $this->job = $job;
        
        $this->getRefs()->setJob($job);
        
        return $this;
    }
    
   
    
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }
    
    public function getUserId()
    {
        return $this->userId;
    }
    
    public function setUser(UserInterface $user)
    {
        $this->user = $user;
        return $this;
    }
    
    public function getUser()
    {
        return $this->user;
    }

    public function setStatus($status)
    {
        if (!$status instanceOf Status) {
            $status = new Status($status);
        } 
        $this->status = $status;
        return $this;
    }
    
    public function changeStatus($status)
    {
        $this->setStatus($status);
        $status = $this->getStatus(); // ensure StatusEntity
        
        $this->getHistory()->addFromStatus($status);
        return $this;
    }
    
    public function getStatus()
    {
        return $this->status;
    }
    
    public function getDateCreated ($format=null)
    {
        if (!$this->dateCreated) {
            $this->setDateCreated('now');
        }
        return null !== $format
            ? strftime($format, $this->dateCreated->getTimestamp())
            : $this->dateCreated;
    }
    
    public function setDateCreated ($dateCreated)
    {
        if (is_string($dateCreated)) {
            $dateCreated = new \DateTime($dateCreated);
        }
        
        if (!$dateCreated instanceOf \DateTime) {
            $dateCreated = new \DateTime();
        }
        
        $this->dateCreated = $dateCreated;
    }
    
    public function getDateModified ($format=null)
    {
        if (!$this->dateModified) {
            $this->setDateModified('now');
        }
        return null !== $format
            ? $this->dateModified->format($format)
            : $this->dateModified;
    }
    
    public function setDateModified ($dateModified)
    {
        if (is_string($dateModified)) {
            $dateCreated = new \DateTime($dateModified);
        }
    
        if (!$dateModified instanceOf \DateTime) {
            $dateModified = new \DateTime();
        }
    
        $this->dateModified = $dateModified;
    }
    
	/**
     * @return the $contact
     */
    public function getContact ()
    {
        return $this->contact;
    }

	/**
     * @param field_type $contact
     */
    public function setContact (EntityInterface $contact)
    {
        $this->contact = $contact;
        return $this;
    }

    public function setSummary($summary)
    {
        $this->summary = (string) $summary;
        return $this;
    }
    
    public function getSummary()
    {
        return $this->summary;
    }
    
	public function setCv(EntityInterface $cv)
	{
	    $this->cv = $cv;
	    return $this;
	}
	
	public function getCv()
	{
	    return $this->cv;
	}
	
	public function setAttachments(Collection $attachments)
	{
	    $this->attachments = $attachments;
	    return $this;
	}
	
	public function getAttachments()
	{
	    return $this->attachments;
	}
	
	public function setHistory(Collection $history)
	{
	    $this->history = $history;
	    return $this;
	}
	
	public function getHistory()
	{
	    return $this->history;
	}
        
	public function setPrivacyPolicyAccepted($privacyPolicy)
	{
	    $this->privacyPolicy = $privacyPolicy;
	    return $this;
	}
        
	public function getPrivacyPolicyAccepted()
	{
	    return $this->privacyPolicy;
	}
	
    public function setReadBy(array $userIds)
    {
        $this->readBy = $userIds;
        return $this;
    }
    
    public function getReadBy()
    {
        return $this->readBy;
    }
    
    public function addReadBy($userOrId)
    {
        if ($userOrId instanceOf UserInterface) {
            $userOrId = $userOrId->getId();
        }
        if (!in_array($userOrId, $this->readBy)) {
            $this->readBy[] = $userOrId;
        }
    }
    
    public function isUnreadBy($userOrId) 
    {
        return !$this->isReadBy($userOrId);
    }
     
    public function isReadBy($userOrId)
    {
        if ($userOrId instanceOf UserInterface) {
            $userOrId = $userOrId->getId();
        }
        
        return in_array($userOrId, $this->readBy);
    }
    
    public function getRefs()
    {
        if (!$this->refs) {
            $this->refs = new InternalReferences();
        }
        return $this->refs;
    }
    
}