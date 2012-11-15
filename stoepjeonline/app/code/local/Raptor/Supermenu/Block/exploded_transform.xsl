<?xml version="1.0" encoding="UTF-8"?>
	<!--
		XSL for exploded menu (nested ul for columns) Note the php namespace
		and xsl:extension-element-prefixes attribute 
		Copyright (c) 2009 Raptor Commerce (http://www.raptorcommerce.com)
	-->
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl"
	extension-element-prefixes="php">
	<xsl:output method='html' />
	
	<xsl:template match="/">
		<!-- <ul id="anav"> -->
		<!-- top level menu items -->
		<xsl:for-each select="menu/menu_item">
			<li>
				<!-- add a custom class for each top level menu item -->
				<xsl:attribute name="class">
						<![CDATA[level0]]> 
						<xsl:if test="@classes">
							<![CDATA[ ]]>
							<xsl:value-of select="@classes" />
						</xsl:if>
					</xsl:attribute>
					
				<xsl:if test="@css_id">
					<xsl:apply-templates select="@css_id" />
				</xsl:if>
				
				<xsl:attribute name="onmouseover">
						<![CDATA[toggleMenu(this, 1)]]> 
					</xsl:attribute>
				<xsl:attribute name="onmouseout">
						<![CDATA[toggleMenu(this, 0)]]> 
					</xsl:attribute>
					
				<!-- renders the link and text for the menu item -->
				<xsl:if test="@display">
					<xsl:apply-templates select="@display" />
				</xsl:if>
				<xsl:if test="link">
					<xsl:apply-templates select="link" />
				</xsl:if>
				
				<!--
					if there are one or more column nodes in the base xml render a
					dropdown
				-->
				<xsl:if test="column">
					<ul class="dropdown">
						<xsl:if test="@style">
							<xsl:attribute name="style">
								<xsl:value-of select="@style" />
							</xsl:attribute>
						</xsl:if>
						<xsl:apply-templates select="logo" />
						<xsl:apply-templates select="column" />
					</ul>
				</xsl:if>
			</li>
		</xsl:for-each>
		<!-- </ul> -->
	</xsl:template>
	
	<xsl:template match="logo">
		<li class="columns">
			<xsl:attribute name="style">
				<xsl:value-of select="@style" />
			</xsl:attribute>
			<img class="logo">
				<xsl:attribute name="src">
					<xsl:value-of select="@src" />
				</xsl:attribute>
			</img>
		</li>
	</xsl:template>
	
	<xsl:template match="column">
		<li>
		<xsl:if test="@style">
			<xsl:attribute name="style">
				<xsl:value-of select="@style" />
			</xsl:attribute>
		</xsl:if>		
		<!-- custom classes for each column -->
			<xsl:attribute name="class">
				<![CDATA[columns]]> 
					<xsl:if test="@classes">
						<![CDATA[ ]]>
						<xsl:value-of select="@classes" />
					</xsl:if>
			</xsl:attribute>
			<xsl:if test="@css_id">
				<xsl:apply-templates select="@css_id" />
			</xsl:if>			
			<!-- if the column has a nested menu_item (level_1) render it -->
			<xsl:if test="menu_item">
				<ul>
					<xsl:apply-templates select="menu_item" mode="level1" />
				</ul>
				<!-- END: level_1 menu items -->
			</xsl:if>
		</li>	
	</xsl:template>
	
	<xsl:template mode="level1" match="menu_item">
	<li>
		<!-- custom classes for level_1 menu_item -->
		<xsl:attribute name="class">
				<![CDATA[level1]]> 
				<xsl:if test="@classes">
					<![CDATA[ ]]>
					<xsl:value-of select="@classes" />
				</xsl:if>
		</xsl:attribute>
		
		<xsl:if test="@css_id">
			<xsl:apply-templates select="@css_id" />
		</xsl:if>		
			
		<!-- renders the link and text for the menu item -->
		<xsl:if test="@display">
			<xsl:apply-templates select="@display" />
		</xsl:if>
		<xsl:if test="link">
			<xsl:apply-templates select="link" />
		</xsl:if>
		
		<!-- level_2 menus -->
		<xsl:if test="menu_item">
			<ul>
				<!-- individual level_2 menu items -->
				<xsl:apply-templates select="menu_item"
					mode="level2"></xsl:apply-templates>
			</ul>
			<!-- END: level_2 menu items -->
		</xsl:if>
	</li>
	</xsl:template>
	
	<xsl:template mode="level2" match="menu_item">
		<li>
			<!-- custom classes for level_2 menus -->
			<xsl:attribute name="class">
				<![CDATA[level2]]> 
				<xsl:if test="@classes">
					<![CDATA[ ]]>
					<xsl:value-of select="@classes" />
				</xsl:if>
			</xsl:attribute>
			<xsl:if test="@css_id">
				<xsl:apply-templates select="@css_id" />
			</xsl:if>
			
			<!-- renders the link and text for the menu item -->
			<xsl:if test="@display">
				<xsl:apply-templates select="@display" />
			</xsl:if>
			<xsl:if test="link">
				<xsl:apply-templates select="link" />
			</xsl:if>
		</li>
	</xsl:template>
	
	<xsl:template match="@display">
		<a>
			<xsl:attribute name="href">
   						   <xsl:value-of select="../@href" />
   			  		</xsl:attribute>
			<xsl:if test="@target">
				<xsl:attribute name="target">
   						   <xsl:value-of select="../@target" />
   			  			</xsl:attribute>
			</xsl:if>
			<span>
				<xsl:value-of select="php:functionString('Raptor_Supermenu_Block_Navigation::localize', .)" />
			</span>
		</a>
	</xsl:template>
	
	<xsl:template match="link">
		<a>
			<xsl:attribute name="href">
            	<xsl:value-of select="href" />
            </xsl:attribute>
			<xsl:if test="target">
				<xsl:attribute name="target">
	    			<xsl:value-of select="target" />
	    		</xsl:attribute>
			</xsl:if>
			<span>
				<xsl:value-of select="display" disable-output-escaping="yes" />
			</span>
		</a>
	</xsl:template>
	
	<xsl:template match="@css_id">
		<xsl:attribute name="id">
        	<xsl:value-of select="." />
        </xsl:attribute>
	</xsl:template>	
</xsl:stylesheet>
