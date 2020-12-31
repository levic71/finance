<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
 
<xsl:variable name="num" select="0"/>
 
<xsl:template match="/">
<xsl:element name="HTML">
<xsl:element name="HEAD"></xsl:element>
<xsl:element name="BODY">
<xsl:element name="TABLE"><xsl:attribute name="STYLE">width: 100%</xsl:attribute>
<xsl:element name="TR"><xsl:attribute name="BGCOLOR">#CCCCCC</xsl:attribute>
<xsl:element name="TD"><xsl:attribute name="ALIGN">CENTER</xsl:attribute><B>ID</B></xsl:element>
<xsl:element name="TD"><xsl:attribute name="ALIGN">CENTER</xsl:attribute><B>Nom</B></xsl:element>
</xsl:element>
<xsl:apply-templates/>
</xsl:element>
</xsl:element>
</xsl:element>
</xsl:template>

<xsl:template match="JORKYBALL">
<xsl:for-each select="CHAMPIONNAT">
<xsl:element name="TR">
<xsl:element name="TD"><xsl:attribute name="ALIGN">LEFT</xsl:attribute><xsl:value-of select="@ID"/></xsl:element>
<xsl:element name="TD"><xsl:attribute name="ALIGN">LEFT</xsl:attribute>
	<xsl:element name="A"><xsl:attribute name="HREF">xml_championnat.php?id_championnat=<xsl:value-of select="@ID"/></xsl:attribute>
		<xsl:value-of select="@NOM"/>
	</xsl:element>
</xsl:element>
</xsl:element>
</xsl:for-each>
</xsl:template>

</xsl:stylesheet>
