<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.1" 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:tei="http://www.tei-c.org/ns/1.0"
  exclude-result-prefixes="tei"
>
  <xsl:import href="glossaire_html.xsl"/>
  
  <xsl:template match="/">
    <sqlite>
      <xsl:apply-templates select="/tei:TEI/tei:text/tei:body/tei:entry"/>
    </sqlite>
  </xsl:template>

  <xsl:template match="tei:entry">
    <xsl:variable name="href">
      <xsl:value-of select="$html"/>
      <xsl:text>glossaire/</xsl:text>
      <xsl:value-of select="@xml:id"/>
      <xsl:text>.html</xsl:text>
    </xsl:variable>
    <!--
    <xsl:message><xsl:value-of select="$href"/></xsl:message>
    avec la déclaration XML pour IE7 (strict mode)
    xml-declaration dérange les chm
    -->
    <xsl:document href="{$href}" 
      doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"
      doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" 
      omit-xml-declaration="yes" 
      encoding="UTF-8" indent="yes"
    >
      <html>
        <head profile="http://dublincore.org/documents/2008/08/04/dc-html/">
          <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
          <link rel="schema.dc" href="http://purl.org/dc/terms/"/>
          <xsl:variable name="label">
            <xsl:call-template name="label"/>
          </xsl:variable>
          <title>
            <xsl:value-of select="$label"/>
            <xsl:text> (par </xsl:text>
            <xsl:variable name="author">
              <xsl:call-template name="author"/>
            </xsl:variable>
            <xsl:value-of select="$author"/>
            <xsl:text>, </xsl:text>
            <xsl:call-template name="date"/>
            <xsl:text>). Glossaire français, Niort : L. Favre, 1883‑1887, t. 9, col. </xsl:text>
            <xsl:variable name="nb" select="preceding::tei:cb[1]/@n"/>
            <xsl:value-of select="$nb"/>
            <xsl:text>. </xsl:text>
          </title>
          <meta name="label">
            <xsl:attribute name="content">
              <xsl:value-of select="substring($label, 1, 1)"/>
              <xsl:value-of select="translate(substring($label, 2), $caps, $mins)"/>
            </xsl:attribute>
          </meta>
          <!-- Articles liés, faire lien vers les articles du Glossarium, 
          utilisé par l'interface pour des inclusions automatiques -->
          <xsl:for-each select=".//tei:ref">
            <xsl:choose>
              <xsl:when test="not(@cRef) and not(@target)"/>
              <xsl:when test="starts-with(@cRef, '#') or starts-with(@target, '#')"/>
              <xsl:otherwise>
                <link rel="dc.relation" href="{@cRef|@target}"/>
              </xsl:otherwise>
            </xsl:choose>
          </xsl:for-each>
          <!-- 
          content="{$label}"/>
          Quelles autres métadonnées utiles ?
          prev ? next ? images ?
          -->
          <link rel="stylesheet" type="text/css" href="../../theme/ducange.css"/>
          <link rel="dc.isPartOf" href="glossaire"/>
          <script type="text/javascript" src="../../theme/ducange.js">//</script>
        </head>
        <body>
          <xsl:call-template name="entry"/>
        </body>
      </html>
    </xsl:document>
  </xsl:template>
    

  

  
  <!-- Pas de lien # -->
  <xsl:template match="@cRef|@target">
    <xsl:attribute name="href">
      <xsl:choose>
        <xsl:when test="starts-with(., '#')">
          <xsl:value-of select="substring(.,2)"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="."/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:attribute>
  </xsl:template>



</xsl:transform>
