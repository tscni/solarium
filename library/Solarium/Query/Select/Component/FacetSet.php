<?php
/**
 * Copyright 2011 Bas de Nooijer. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this listof conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDER AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * The views and conclusions contained in the software and documentation are
 * those of the authors and should not be interpreted as representing official
 * policies, either expressed or implied, of the copyright holder.
 *
 * @copyright Copyright 2011 Bas de Nooijer <solarium@raspberry.nl>
 * @license http://github.com/basdenooijer/solarium/raw/master/COPYING
 *
 * @package Solarium
 * @subpackage Query
 */

/**
 * MoreLikeThis component
 *
 * @link http://wiki.apache.org/solr/MoreLikeThis
 * 
 * @package Solarium
 * @subpackage Query
 */
class Solarium_Query_Select_Component_FacetSet extends Solarium_Query_Select_Component
{

    /**
     * Component type
     * 
     * @var string
     */
    protected $_type = Solarium_Query_Select::COMPONENT_FACETSET;

    /**
     * Default options
     * 
     * @var array
     */
    protected $_options = array();

    /**
     * Facets
     *
     * @var array
     */
    protected $_facets = array();

    /**
     * Initialize options
     *
     * Several options need some extra checks or setup work, for these options
     * the setters are called.
     *
     * @return void
     */
    protected function _init()
    {
        if (isset($this->_options['facet'])) {
            foreach ($this->_options['facet'] AS $key => $config) {
                if (!isset($config['key'])) {
                    $config['key'] = $key;
                }

                $this->addFacet($config);
            }
        }
    }

    /**
     * Limit the terms for faceting by a prefix
     *
     * This is a global value for all facets in this facetset
     *
     * @param string $prefix
     * @return Solarium_Query_Select_Component_FacetSet Provides fluent interface
     */
    public function setPrefix($prefix)
    {
        return $this->_setOption('prefix', $prefix);
    }

    /**
     * Get the facet prefix
     *
     * This is a global value for all facets in this facetset
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->getOption('prefix');
    }

    /**
     * Set the facet sort order
     *
     * Use one of the SORT_* constants as the value
     *
     * This is a global value for all facets in this facetset
     *
     * @param string $sort
     * @return Solarium_Query_Select_Component_FacetSet Provides fluent interface
     */
    public function setSort($sort)
    {
        return $this->_setOption('sort', $sort);
    }

    /**
     * Get the facet sort order
     *
     * This is a global value for all facets in this facetset
     *
     * @return string
     */
    public function getSort()
    {
        return $this->getOption('sort');
    }

    /**
     * Set the facet limit
     *
     *  This is a global value for all facets in this facetset
     *
     * @param int $limit
     * @return Solarium_Query_Select_Component_FacetSet Provides fluent interface
     */
    public function setLimit($limit)
    {
        return $this->_setOption('limit', $limit);
    }

    /**
     * Get the facet limit
     *
     * This is a global value for all facets in this facetset
     *
     * @return string
     */
    public function getLimit()
    {
        return $this->getOption('limit');
    }

    /**
     * Set the facet mincount
     *
     * This is a global value for all facets in this facetset
     *
     * @param int $mincount
     * @return Solarium_Query_Select_Component_FacetSet Provides fluent interface
     */
    public function setMinCount($minCount)
    {
        return $this->_setOption('mincount', $minCount);
    }

    /**
     * Get the facet mincount
     *
     * This is a global value for all facets in this facetset
     *
     * @return int
     */
    public function getMinCount()
    {
        return $this->getOption('mincount');
    }

    /**
     * Set the missing count option
     *
     * This is a global value for all facets in this facetset
     *
     * @param boolean $missing
     * @return Solarium_Query_Select_Component_FacetSet Provides fluent interface
     */
    public function setMissing($missing)
    {
        return $this->_setOption('missing', $missing);
    }

    /**
     * Get the facet missing option
     *
     * This is a global value for all facets in this facetset
     *
     * @return boolean
     */
    public function getMissing()
    {
        return $this->getOption('missing');
    }

    /**
     * Add a facet
     *
     * @param Solarium_Query_Select_Component_Facet|array $facet
     * @return Solarium_Query Provides fluent interface
     */
    public function addFacet($facet)
    {
        if (is_array($facet)) {
            $className = 'Solarium_Query_Select_Component_Facet_'.ucfirst($facet['type']);
            $facet = new $className($facet);
        }

        $key = $facet->getKey();

        if (0 === strlen($key)) {
            throw new Solarium_Exception('A facet must have a key value');
        }

        if (array_key_exists($key, $this->_facets)) {
            throw new Solarium_Exception('A facet must have a unique key value'
                . ' within a query');
        }

        $this->_facets[$key] = $facet;
        return $this;
    }

    /**
     * Add multiple facets
     *
     * @param array $facets
     * @return Solarium_Query Provides fluent interface
     */
    public function addFacets(array $facets)
    {
        foreach ($facets AS $key => $facet) {

            // in case of a config array: add key to config
            if (is_array($facet) && !isset($facet['key'])) {
                $facet['key'] = $key;
            }

            $this->addFacet($facet);
        }

        return $this;
    }

    /**
     * Get a facet
     *
     * @param string $key
     * @return string
     */
    public function getFacet($key)
    {
        if (isset($this->_facets[$key])) {
            return $this->_facets[$key];
        } else {
            return null;
        }
    }

    /**
     * Get all facets
     *
     * @return array
     */
    public function getFacets()
    {
        return $this->_facets;
    }

    /**
     * Remove a single facet by key
     *
     * @param string $key
     * @return Solarium_Query Provides fluent interface
     */
    public function removeFacet($key)
    {
        if (isset($this->_facets[$key])) {
            unset($this->_facets[$key]);
        }

        return $this;
    }

    /**
     * Remove all facets
     *
     * @return Solarium_Query Provides fluent interface
     */
    public function clearFacets()
    {
        $this->_facets = array();
        return $this;
    }

    /**
     * Set multiple facets
     *
     * This overwrites any existing facets
     *
     * @param array $facets
     */
    public function setFacets($facets)
    {
        $this->clearFacets();
        $this->addFacets($facets);
    }

}