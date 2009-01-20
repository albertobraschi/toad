<?php

/**
 * class Page
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @since  0.1
 */

class Page extends Record
{
    const TABLE_NAME = 'page';
    
    const STATUS_DRAFT = 1;
    const STATUS_REVIEWED = 50;
    const STATUS_PUBLISHED = 100;
    const STATUS_HIDDEN = 101;
    
    public $title;
    public $slug;
    public $breadcrumb;
    public $keywords;
    public $description;
    public $parent_id;
    public $layout_id;
    public $behavior_id;
    public $status_id;
    public $comment_status;
    
    public $created_on;
    public $published_on;
    public $updated_on;
    public $created_by_id;
    public $updated_by_id;
    public $position;
    public $is_protected;
    
    public $behavior;
    
    /* Methods for Record class. */
    
    public function columns() {
        return array('title', 'slug', 'breadcrumb', 'keywords', 'description', 'parent_id', 'layout_id', 
                     'behavior_id', 'status_id', 'comment_status', 'created_on', 'published_on', 'updated_on', 
                     'created_by_id', 'updated_by_id', 'position', 'is_protected', 'id');
    }
    
    public function beforeInsert()
    {
        $this->created_on = date('Y-m-d H:i:s');
        $this->created_by_id = AuthUser::getId();

        $this->updated_on = $this->created_on;
        $this->updated_by_id = $this->created_by_id;

        if ($this->status_id == Page::STATUS_PUBLISHED) {
            $this->published_on = date('Y-m-d H:i:s');            
        }

        return true;
    }
    
    public function beforeUpdate()
    {
        /* TODO: Figure out logic behind xxx_on_time variables
        $this->created_on = $this->created_on . ' ' . $this->created_on_time;
        unset($this->created_on_time);
        */
        
        if (! empty($this->published_on)) {
            /*
            $this->published_on = $this->published_on . ' ' . $this->published_on_time;
            unset($this->published_on_time);
            */
        } else if ($this->status_id == Page::STATUS_PUBLISHED) {
            $this->published_on = date('Y-m-d H:i:s');
        }
        
        $this->updated_by_id = AuthUser::getId();
        $this->updated_on = date('Y-m-d H:i:s');
        
        return true;
    }

    /* Tag related methods. */

    public function tags()
    {
        return Tag::findByPageId($this->id());
    }
    
    public function saveTags($tags)
    {
        if (is_string($tags)) {
            $tags = explode(',', $tags);            
        }
        
        $tags = array_map('trim', $tags);
        $tags = array_filter($tags);
        
        $current_tags = array();
        foreach($this->tags() as $tag) {
            $current_tags[] = $tag->name();            
        }
        
        // no tag before! no tag now! ... nothing to do!
        if (count($tags) == 0 && count($current_tags) == 0) {
            return;            
        }
        // delete all tags
        if (count($tags) == 0) {
            return Record::deleteWhere('PageTag', 'page_id=?', array($this->id));
        } else {
            $old_tags = array_diff($current_tags, $tags);
            $new_tags = array_diff($tags, $current_tags);
            
            // insert all tags in the tag table and then populate the page_tag table
            foreach ($new_tags as $index => $tag_name) {
                if (! empty($tag_name)) {
                    // try to get it from tag list, if not we add it to the list
                    if (! $tag = Tag::findByName($tag_name)) {
                        $tag = new Tag(array('name' => trim($tag_name)));                        
                    }
                    
                    $tag->save();
                    
                    // create the relation between the page and the tag
                    $tag = new PageTag(array('page_id' => $this->id, 'tag_id' => $tag->id));
                    $tag->save();
                }
            }
            
            // remove all old tag
            foreach ($old_tags as $index => $tag_name) {
                // get the id of the tag
                $tag = Tag::findByName($tag_name);
                Record::deleteWhere('PageTag', 'page_id=? AND tag_id=?', array($this->id, $tag->id));
                $tag->save();
            }
        }
    }
    
    
    public function updater() {
        return User::findById($this->updatedById());
    }

    public function creator() {
        return User::findById($this->createdById());
    }
    
    public function author() {
        return $this->creator();
    }
    
    public function parent() {
        return Page::findById($this->parentId());
    }
    
    public function children($params=array(), $include_hidden = false) {
        if (isset($params['where'])) {
            $params['where'] .= ' AND ';
        } else {
            $params['where'] = '';            
        }
        if ($include_hidden) {
            $params['where'] .= sprintf("parent_id=%d AND status_id>=%d", 
                                        $this->id(), Page::STATUS_PUBLISHED);                        
        } else {
            $params['where'] .= sprintf("parent_id=%d AND status_id<%d", 
                                        $this->id(), Page::STATUS_HIDDEN);                        
        }
        
        $class = __CLASS__;
        if ($this->behaviorId()) {
            $class = Behavior::loadPageHack($this->behaviorId());
        }
        
        return Page::find($params, $class);
    }
    
    public function parts($name=null) {
        if ($name) {
            return PagePart::findByNameAndPageId($name, $this->id());                        
        } else {
            return PagePart::findByPageId($this->id());            
        }
    }
    
    public function layout() {
        if ($this->layoutId()) {
            return Layout::findById($this->layoutId());            
        } else {
            return $this->parent()->layout();
        }
    }
        
    public function includeSnippet($name)
    {
        if ($snippet = Snippet::findByName($name)) {
            eval('?>' . $snippet->contentHtml());
        }
    }
    
    public function url($with_suffix = true) {        
        if ($this->parent()) {
            if ($with_suffix) {
                $url = trim($this->parent()->url(false) . '/'. $this->slug(), '/') . URL_SUFFIX;                
            } else {
                $url = trim($this->parent()->url(false) . '/'. $this->slug(), '/');               
            }
        } else {
            $url = BASE_URL . trim('/'. $this->slug(), '/');            
        }
        return $url;
    }
    
    public function link($label=null, $options='')
    {
        if ($label == null) {
            $label = $this->title();            
        }

        return sprintf('<a href="%s" title="%s" %s>%s</a>',
            $this->url(),
            $this->title(),
            $options,
            $label
            );
    }


    /* TODO: This should have inherit. */
    public function hasContent($part) { 
        return $this->parts($part);
    }

    
    public function content($part='body', $inherit=false)
    {
        // if part exist we generate the content en execute it!
        if ($this->parts($part)) {
            ob_start();
            print $part;
            eval('?>' . $this->parts($part)->contentHtml());
            $out = ob_get_contents();
            ob_end_clean();
            return $out;
        } else if ($inherit && $this->parent()) {
            return $this->parent()->content($part, true);
        }
    }

    public static function childrenOf($id)
    {
        return self::find(array('where' => 'parent_id='.$id, 'order' => 'position, created_on DESC'));
    }
    
    public static function hasChildren($id)
    {
        return (boolean) self::countFrom('Page', 'parent_id = '.(int)$id);
    }
    
    public static function cloneTree($page, $parent_id)
    {
        /* This will hold new id of root of cloned tree. */
        static $new_root_id = false;
        
        /* Clone passed in page. */
        $clone = Record::findByIdFrom('Page', $page->id);
        $clone->parent_id = (int)$parent_id;
        $clone->id = null;
        $clone->title .= " (copy)";
        $clone->save();
        
        /* Also clone the page parts. */
        $page_part = PagePart::findByPageId($page->id);
        if (count($page_part)) {
            foreach ($page_part as $part) {
                $part->page_id = $clone->id;
                $part->id = null;
                $part->save();
            }
        }
        
        /* This gets set only once even when called recursively. */
        if (!$new_root_id) {
            $new_root_id = $clone->id;
        }

        /* Clone and update childrens parent_id to clones new id. */
        if (Page::hasChildren($page->id)) {
            foreach (Page::childrenOf($page->id) as $child) {
                Page::cloneTree($child, $clone->id);
            }
        }
        
        return $new_root_id;
    }
        
    /* We need these before PHP 5.3. Older do not have late static binding. */
    static function find($params=null, $class=__CLASS__) {
        /* Assume we should call Record finder. */
        if (is_array($params)) {
            return parent::find($params, $class);
        } else {
        /* Maintain BC. Assume string mean find by URI. */    
            return Page::findByUri($params);
        }
    }

    static function findById($id, $class=__CLASS__) {
        return parent::findById($id, $class);
    }
    
    static function count($params=null, $class=__CLASS__) {
        return parent::count($params, $class);
    }
    
    /* These are just a helper finders. */
    
    /* TODO: This should return an array not just one.*/
    public static function findBigSister($id, $class=__CLASS__) {
        $params['where'] = sprintf('parent_id=%d', $id);
        $params['order'] = 'id DESC';
        $sql = Record::buildSql($params, $class);
        return self::connection()->query($sql, PDO::FETCH_CLASS, $class)->fetch();
    }

    public static function findByParentId($id, $class=__CLASS__) {
        $params['where'] = sprintf('parent_id=%d', $id);
        return parent::find($params, $class);            
    }

    public static function findByUri($uri, $class=__CLASS__) {

        //print "Page::findByUri($uri)";
        $uri = trim($uri, '/');

        $has_behavior = false;

        $urls = array_merge(array(''), explode_uri($uri));
        $url = '';
        $parent = new Page;
        $parent->id(0);


        foreach ($urls as $page_slug) {
            $url = ltrim($url . '/' . $page_slug, '/');
            if ($page = Page::findBySlugAndParentId($page_slug, $parent->id())) {
                if ($page->behaviorId()) {
                    $has_behavior= true;
                    // add a instance of the behavior with the name of the behavior 
                    $params = explode_uri(substr($uri, strlen($url)));
                    print_r($params);
                    $page->behavior(Behavior::load($page->behaviorId(), $page, $params));
                    return $page;
                }
            } else {
                break;
            }
            $parent = $page;  
        } 
        return (!$page && $has_behavior) ? $parent: $page;
    }

    public static function findBySlugAndParentId($slug, $parent_id=0, $class=__CLASS__) {
        /* TODO: Behaviour pagehack seems kludgish. */
        $parent = Page::findById($parent_id);
        if ($parent && $parent->behaviorId()) {
            $class = Behavior::loadPageHack($parent->behaviorId());
        }
        $params['where'] = sprintf("slug=%s AND parent_id=%d", self::connection()->quote($slug), $parent_id);
        $sql = Record::buildSql($params, $class);
       // print_r(self::connection()->query($sql, PDO::FETCH_CLASS, $class)->fetch());
        return self::connection()->query($sql, PDO::FETCH_CLASS, $class)->fetch();
    }
    
    
    public function show() {
        if ($this->layout()) {
            header('Content-Type: '. $this->layout()->contentType() . '; charset=UTF-8');
            eval('?>' . $this->layout()->content());
        }
    }
    
    
    /* TODO: Everything below here is just copy / paste. */
    
    public function date($format='%a, %e %b %Y', $which_one='created')
    {
        if ($which_one == 'update' || $which_one == 'updated')
            return strftime($format, strtotime($this->updated_on));
        else if ($which_one == 'publish' || $which_one == 'published')
            return strftime($format, strtotime($this->published_on));
        else
            return strftime($format, strtotime($this->created_on));
    }

    public function breadcrumbs($separator='&gt;')
    {
        $url = '';
        $path = '';
        $paths = explode('/', '/'.$this->slug);
        $nb_path = count($paths);

        $out = '<div class="breadcrumb">'."\n";

        if ($this->parent)
            $out .= $this->parent->_inversedBreadcrumbs($separator);

        return $out . '<span class="breadcrumb-current">'.$this->breadcrumb().'</span></div>'."\n";

    }

    private function _inversedBreadcrumbs($separator)
    {
        $out = '<a href="'.$this->url().'" title="'.$this->breadcrumb.'">'.$this->breadcrumb.'</a> <span class="breadcrumb-separator">'.$separator.'</span> '."\n";

        if ($this->parent)
            return $this->parent->_inversedBreadcrumbs($separator) . $out;

        return $out;
    }

}