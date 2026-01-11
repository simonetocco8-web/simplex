<?php
/**
 * Created by JetBrains PhpStorm.
 * User: marcello
 * Date: 26/04/13
 * Time: 16.37
 * To change this template use File | Settings | File Templates.
 */

class Model_Sdm2Story_Repository
{
    /**
     * Users mysql mapper
     *
     * @var Model_Sdm_Mapper
     */
    protected $_sdmStoryMapper;

    public function __construct()
    {
        $this->_sdmStoryMapper = new Model_Sdm2Story_Mapper();
    }

    public function getNewSdmStory()
    {
        $sdm = new Model_Sdm2Story();
        return $sdm;
    }

    public function find($id)
    {
        $sdm = $this->_sdmStoryMapper->find($id);
        return $sdm;
    }

    public function findAllWithDependenciesBySdmId($sdm_id)
    {
        $db = $this->_sdmStoryMapper->getDbAdapter();

        $select = $db->select();

        $select->from('sdm_story', array(
            'sdm_story_id',
            'created_by',
            'date_created',
            'modified_by',
            'date_modified',
            'active',
            'id_status',
            'text1',
            'text2',
            'date1',
            'date2',
            'id_user',
        ))
            ->joinLeft(array('u1' => 'users'), 'u1.user_id = sdm_story.created_by', array('creator' => 'u1.username'))
            ->joinLeft(array('u2' => 'users'), 'u2.user_id = sdm_story.modified_by', array('modifier' => 'u2.username'))
            ->joinLeft(array('u3' => 'users'), 'u3.user_id = sdm_story.id_user', array('user' => 'u3.username'));

        $select->where('id_sdm = ?', $sdm_id);
        $select->order('sdm_story_id asc');

        $data = $db->fetchAll($select);

        $stories = array();

        foreach($data as $story)
        {
            $sdm_story = $this->getNewSdmStory();

            $sdm_story->setData($story);

            $stories[] = $sdm_story;
        }

        return $stories;
    }

    public function save($sdm)
    {
        return $this->_sdmStoryMapper->save($sdm);
    }

    public function fetch($data = array())
    {
        $count = isset($data['count']) && $data['count'];

        $db = $this->_sdmStoryMapper->getDbAdapter();
        $select = $db->select();

        $select->from('sdm2', array('sdm_id', 'id_status', 'code', 'with_prevention'))
            ->joinLeft(array('u1' => 'users'), 'u1.user_id = sdm2.created_by', array('creator' => 'username'))

            // segnalatore
            ->joinLeft(array('s2' => 'sdm_story'), 's2.id_sdm = sdm2.sdm_id and s2.id_status = 2 and s2.active = 1', array('problem' => 'text1', 'date_problem' => 'date1'))
            ->joinLeft(array('u2' => 'users'), 'u2.user_id = s2.id_user', array('responsible' => 'username'))

            // risolutore
            ->joinLeft(array('s3' => 'sdm_story'), 's3.id_sdm = sdm2.sdm_id and s3.id_status = 3 and s3.active = 1', array('treatment' => 'text1', 'cause' => 'text2', 'date_expected_resolution' => 'date1', 'date_set_solver' => 'date2'))
            ->joinLeft(array('u3' => 'users'), 'u3.user_id = s3.id_user', array('solver' => 'username'))

            // risolutore
            ->joinLeft(array('s4' => 'sdm_story'), 's4.id_sdm = sdm2.sdm_id and s4.id_status = 4 and s4.active = 1', array('resolution' => 'text1', 'date_resolution' => 'date1'))
            ->joinLeft(array('u4' => 'users'), 'u4.user_id = s4.id_user', array('solver' => 'username'))

            // verification
            ->joinLeft(array('s5' => 'sdm_story'), 's5.id_sdm = sdm2.sdm_id and s5.id_status = 5 and s5.active = 1', array('verification' => 'text1', 'date_verification' => 'date1'))
            ->joinLeft(array('u5' => 'users'), 'u5.user_id = s5.id_user', array('solver' => 'username'))
        ;
            // TODO: DATE_FEEDBACK

        $aut = Zend_Auth::getInstance()->getIdentity();
        $user_id = $aut->user_id;
        //$select->where('id_status <> 0 || sdm.created_by = ' . $user_id);

        if(!$count)
        {
            if(isset($data['_s']) && trim($data['_s']) != '')
            {
                $select->order($data['_s'] . ' ' . (isset($data['_d']) ? $data['_d'] : 'ASC' ));
            }
            else
            {
                $select->order('date_problem DESC');
            }
        }

        if (isset($data['code']) && $data['code'] != '')
        {
            $select->where('code like ' . $db->quote('%' . $data['code'] . '%'));
        }

        if (isset($data['created_by']) && !empty($data['created_by']))
        {
            $select->where('sdm2.created_by = ' . implode(' or sdm2.created_by = ', $data['created_by']));
        }

        if (isset($data['id_status']) && !empty($data['id_status']))
        {
            $select->where('sdm2.id_status = ' . implode(' or sdm2.id_status = ', $data['id_status']));
        }

        if (isset($data['description']) && $data['description'] != '')
        {
            $select->where('problem like ' . $db->quote('%' . $data['description'] . '%'));
        }

        if (isset($data['date_problem']) && $data['date_problem'] != '')
        {
            $parts = explode('-', $data['date_problem']);

            if(count($parts) == 2)
            {
                $select->where('date_problem >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and date_problem <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
            }
            else
            {
                $select->where('date_problem = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
            }
        }

        if (isset($data['cause']) && $data['cause'] != '')
        {
            $select->where('cause like ' . $db->quote('%' . $data['cause'] . '%'));
        }

        if (isset($data['area']) && $data['area'] != '')
        {
            $select->where('area like ' . $db->quote('%' . $data['area'] . '%'));
        }

        if (isset($data['id_responsible']) && !empty($data['id_responsible']))
        {
            $select->where('sdm2.id_responsible = ' . implode(' or sdm2.id_responsible = ', $data['id_responsible']));
        }

        if (isset($data['date_feedback']) && $data['date_feedback'] != '')
        {
            $parts = explode('-', $data['date_feedback']);

            if(count($parts) == 2)
            {
                $select->where('date_feedback >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and date_feedback <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
            }
            else
            {
                $select->where('date_feedback = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
            }
        }

        if (isset($data['id_solver']) && !empty($data['id_solver']))
        {
            $select->where('sdm2.id_solver = ' . implode(' or sdm2.id_solver = ', $data['id_solver']));
        }

        if (isset($data['treatment']) && $data['treatment'] != '')
        {
            $select->where('treatment like ' . $db->quote('%' . $data['treatment'] . '%'));
        }

        if (isset($data['date_expected_resolution']) && $data['date_expected_resolution'] != '')
        {
            $parts = explode('-', $data['date_expected_resolution']);

            if(count($parts) == 2)
            {
                $select->where('date_expected_resolution >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and date_expected_resolution <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
            }
            else
            {
                $select->where('date_expected_resolution = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
            }
        }

        if (isset($data['resolution']) && $data['resolution'] != '')
        {
            $select->where('treatment like ' . $db->quote('%' . $data['treatment'] . '%'));
        }

        if (isset($data['date_resolution']) && $data['date_resolution'] != '')
        {
            $parts = explode('-', $data['date_resolution']);

            if(count($parts) == 2)
            {
                $select->where('date_resolution >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and date_resolution <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
            }
            else
            {
                $select->where('date_resolution = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
            }
        }

        if (isset($data['verification']) && $data['verification'] != '')
        {
            $select->where('verification like ' . $db->quote('%' . $data['verification'] . '%'));
        }

        if (isset($data['date_verification']) && $data['date_verification'] != '')
        {
            $parts = explode('-', $data['date_verification']);

            if(count($parts) == 2)
            {
                $select->where('date_verification >= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))) . ' and date_verification <= ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[1]))));
            }
            else
            {
                $select->where('date_verification = ' . $db->quote(Maco_Utils_DbDate::toDb(trim($parts[0]))));
            }
        }

        if (isset($data['responsible']) && !empty($data['responsible']))
        {
            $select->where(' sdm_id in (select sr.id_sdm from sdm_responsibles sr where sr.id_user in (' . implode(', ', $data['responsible']) . '))');
        }


        if($count)
        {
            return count($db->fetchAll($select));
        }

        return $db->fetchAll($select);
    }

    public function getNextYearAndProgr()
    {
        $db = $this->_sdmStoryMapper->getDbAdapter();

        $year = date('Y');
        $progr = '1';
        $last_code = $db->fetchOne('select code from sdm2 where code <> \'\' and code is not null order by sdm_id DESC limit 0, 1');
        $parts = explode('-', $last_code);
        if($parts[0] == $year)
        {
            // stesso anno
            $progr = ++$parts[1];
        }
        else
        {
            // anno nuovo
        }
        return array($year, $progr);
    }
}