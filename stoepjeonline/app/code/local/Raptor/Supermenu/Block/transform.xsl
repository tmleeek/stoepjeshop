<?xml version="1.0" encoding="UTF-8"?>
<!-- 
   XSL for standard (Magento) navigation menu
   Note the php namespace and xsl:extension-element-prefixes attribute
   
   Copyright (c) 2009 Raptor Commerce (http://www.raptorcommerce.com)
 -->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl" extension-element-prefixes="php">
	<xsl:output method='html' />
	<xsl:template match="menu/menu_item">
		<xsl:choose>
		<xsl:when test="column">
		<li class="level0 parent" onmouseout="toggleMenu(this,0)"
			onmouseover="toggleMenu(this,1)">
			<a>
				<xsl:attribute name="href">
					<xsl:value-of select="@link" />
				</xsl:attribute>
			    <span>
                    <xsl:value-of select="php:functionString('Raptor_Custommenu_Block_Navigation::localize', @display)" />
                </span>
			</a>
			<ul class="level0">
				<xsl:apply-templates mode="level1" />
			</ul>
		</li>
		</xsl:when>
		<xsl:otherwise>
		<li class="level0">
			<a>
				<xsl:attribute name="href">
					<xsl:value-of select="@link" />
				</xsl:attribute>
   			   <span>	
                  <xsl:value-of select="php:functionString('Raptor_Custommenu_Block_Navigation::localize', @display)" />
               </span>
			</a>
		</li>		
		</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<xsl:template match="column/menu_item" mode="level1">
		<xsl:for-each select=".">
			<xsl:choose>
				<xsl:when test="menu_item">
					<li class="level1 parent"
						onmouseout="toggleMenu(this,0)"
						onmouseover="toggleMenu(this,1)">
						<a>
							<xsl:attribute name="href">
							<xsl:choose>
								<xsl:when test="@link">
									<xsl:value-of select="@link" />
								</xsl:when>
								<xsl:otherwise>
									<![CDATA[#]]>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:attribute>
							<span>
								<xsl:value-of select="php:functionString('Raptor_Custommenu_Block_Navigation::localize', @display)" />
							</span>
						</a>
						<ul class="level1">
							<xsl:for-each select="menu_item">
								<li class="level2">
									<a>
										<xsl:attribute name="href">
									<xsl:choose>
										<xsl:when test="@link">
											<xsl:value-of
														select="@link" />
										</xsl:when>
										<xsl:otherwise>
											<![CDATA[#]]>
										</xsl:otherwise>
									</xsl:choose>
								</xsl:attribute>
										<span>
                                          <xsl:value-of select="php:functionString('Raptor_Custommenu_Block_Navigation::localize', @display)" />
										</span>
									</a>
								</li>
							</xsl:for-each>
						</ul>
					</li>
				</xsl:when>
				<xsl:otherwise>
					<li class="level0"
						onmouseout="toggleMenu(this,0)"
						onmouseover="toggleMenu(this,1)">
						<a>
							<xsl:attribute name="href">
							<xsl:choose>
								<xsl:when test="@link">
									<xsl:value-of select="@link" />
								</xsl:when>
								<xsl:otherwise>
									<![CDATA[#]]>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:attribute>
							<span>
								<xsl:value-of select="php:functionString('Raptor_Custommenu_Block_Navigation::localize', @display)" />
							</span>
						</a>
					</li>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:for-each>
	</xsl:template>
</xsl:stylesheet>
