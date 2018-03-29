<?php

namespace HeimrichHannot\GoogleMapsBundle\Backend;

use Contao\Backend;

class Overlay extends Backend
{
    public function listChildren($arrRow)
    {
        return '<div class="tl_content_left">' . ($arrRow['title'] ?: $arrRow['id']) . ' <span style="color:#b3b3b3; padding-left:3px">[' .
               \Date::parse(\Contao\Config::get('datimFormat'), trim($arrRow['dateAdded'])) . ']</span></div>';
    }

    public function checkPermission()
    {
        $user = \Contao\BackendUser::getInstance();
        $database = \Contao\Database::getInstance();

        if ($user->isAdmin)
        {
            return;
        }

        // Set the root IDs
        if (!is_array($user->contao_google_maps_bundles) || empty($user->contao_google_maps_bundles))
        {
            $root = [0];
        }
        else
        {
            $root = $user->contao_google_maps_bundles;
        }

        $id = strlen(\Contao\Input::get('id')) ? \Contao\Input::get('id') : CURRENT_ID;

        // Check current action
        switch (\Contao\Input::get('act'))
        {
            case 'paste':
                // Allow
                break;

            case 'create':
                if (!strlen(\Contao\Input::get('pid')) || !in_array(\Contao\Input::get('pid'), $root))
                {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to create google_map_overlay items in google_map_overlay archive ID ' . \Contao\Input::get('pid') . '.');
                }
                break;

            case 'cut':
            case 'copy':
                if (!in_array(\Contao\Input::get('pid'), $root))
                {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to ' . \Contao\Input::get('act') . ' google_map_overlay item ID ' . $id . ' to google_map_overlay archive ID ' . \Contao\Input::get('pid') . '.');
                }
            // NO BREAK STATEMENT HERE

            case 'edit':
            case 'show':
            case 'delete':
            case 'toggle':
            case 'feature':
                $objArchive = $database->prepare("SELECT pid FROM tl_google_map_overlay WHERE id=?")
                    ->limit(1)
                    ->execute($id);

                if ($objArchive->numRows < 1)
                {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid google_map_overlay item ID ' . $id . '.');
                }

                if (!in_array($objArchive->pid, $root))
                {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to ' . \Contao\Input::get('act') . ' google_map_overlay item ID ' . $id . ' of google_map_overlay archive ID ' . $objArchive->pid . '.');
                }
                break;

            case 'select':
            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
            case 'cutAll':
            case 'copyAll':
                if (!in_array($id, $root))
                {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to access google_map_overlay archive ID ' . $id . '.');
                }

                $objArchive = $database->prepare("SELECT id FROM tl_google_map_overlay WHERE pid=?")
                    ->execute($id);

                if ($objArchive->numRows < 1)
                {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid google_map_overlay archive ID ' . $id . '.');
                }

                /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface $session */
                $session = \System::getContainer()->get('session');

                $session = $session->all();
                $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $objArchive->fetchEach('id'));
                $session->replace($session);
                break;

            default:
                if (strlen(\Contao\Input::get('act')))
                {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid command "' . \Contao\Input::get('act') . '".');
                }
                elseif (!in_array($id, $root))
                {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to access google_map_overlay archive ID ' . $id . '.');
                }
                break;
        }
    }

    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        $user = \Contao\BackendUser::getInstance();

        if (strlen(\Contao\Input::get('tid')))
        {
            $this->toggleVisibility(\Contao\Input::get('tid'), (\Contao\Input::get('state') === '1'), (@func_get_arg(12) ?: null));
            $this->redirect($this->getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$user->hasAccess('tl_google_map_overlay::published', 'alexf'))
        {
            return '';
        }

        $href .= '&amp;tid='.$row['id'].'&amp;state='.($row['published'] ? '' : 1);

        if (!$row['published'])
        {
            $icon = 'invisible.svg';
        }

        return '<a href="'.$this->addToUrl($href).'" title="'.\StringUtil::specialchars($title).'"'.$attributes.'>'.\Image::getHtml($icon, $label, 'data-state="' . ($row['published'] ? 1 : 0) . '"').'</a> ';
    }

    public function toggleVisibility($intId, $blnVisible, \DataContainer $dc=null)
    {
        $user = \Contao\BackendUser::getInstance();
        $database = \Contao\Database::getInstance();

        // Set the ID and action
        \Contao\Input::setGet('id', $intId);
        \Contao\Input::setGet('act', 'toggle');

        if ($dc)
        {
            $dc->id = $intId; // see #8043
        }

        // Trigger the onload_callback
        if (is_array($GLOBALS['TL_DCA']['tl_google_map_overlay']['config']['onload_callback']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_google_map_overlay']['config']['onload_callback'] as $callback)
            {
                if (is_array($callback))
                {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($dc);
                }
                elseif (is_callable($callback))
                {
                    $callback($dc);
                }
            }
        }

        // Check the field access
        if (!$user->hasAccess('tl_google_map_overlay::published', 'alexf'))
        {
            throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to publish/unpublish google_map_overlay item ID ' . $intId . '.');
        }

        // Set the current record
        if ($dc)
        {
            $objRow = $database->prepare("SELECT * FROM tl_google_map_overlay WHERE id=?")
                ->limit(1)
                ->execute($intId);

            if ($objRow->numRows)
            {
                $dc->activeRecord = $objRow;
            }
        }

        $objVersions = new \Versions('tl_google_map_overlay', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_google_map_overlay']['fields']['published']['save_callback']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_google_map_overlay']['fields']['published']['save_callback'] as $callback)
            {
                if (is_array($callback))
                {
                    $this->import($callback[0]);
                    $blnVisible = $this->{$callback[0]}->{$callback[1]}($blnVisible, $dc);
                }
                elseif (is_callable($callback))
                {
                    $blnVisible = $callback($blnVisible, $dc);
                }
            }
        }

        $time = time();

        // Update the database
        $database->prepare("UPDATE tl_google_map_overlay SET tstamp=$time, published='" . ($blnVisible ? '1' : '') . "' WHERE id=?")
            ->execute($intId);

        if ($dc)
        {
            $dc->activeRecord->tstamp = $time;
            $dc->activeRecord->published = ($blnVisible ? '1' : '');
        }

        // Trigger the onsubmit_callback
        if (is_array($GLOBALS['TL_DCA']['tl_google_map_overlay']['config']['onsubmit_callback']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_google_map_overlay']['config']['onsubmit_callback'] as $callback)
            {
                if (is_array($callback))
                {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($dc);
                }
                elseif (is_callable($callback))
                {
                    $callback($dc);
                }
            }
        }

        $objVersions->create();
    }
}