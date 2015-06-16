<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.1" 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:tei="http://www.tei-c.org/ns/1.0"
  exclude-result-prefixes="tei"
>
  <xsl:import href="ducange_html.xsl"/>
  <xsl:param name="corpus">glossaire</xsl:param>

  <!-- Lien vers le Du Cange -->
  <xsl:template match="@cRef[starts-with(., 'http://ducange')]">
    <xsl:attribute name="href">
      <xsl:value-of select="."/>
    </xsl:attribute>
  </xsl:template>
  
  <!-- Un article -->
  <xsl:template name="entry" match="tei:entry">
    <div class="glossaire" id="{@xml:id}">
      <xsl:apply-templates/>
    </div>
  </xsl:template>

  <!-- alinéas, TODO sous-vedette en form ?  -->
  <xsl:template match="tei:dictScrap">
    <div class="dictScrap">
      <xsl:apply-templates/>
      <xsl:if test="not(following-sibling::tei:dictScrap)">
        <xsl:text> </xsl:text>
        <span class="bibl">
          <xsl:text>(</xsl:text>
          <i class="title">Glossaire français</i>
          <xsl:text>, Niort : L. Favre, 1883‑1887, t. IX, col. </xsl:text>
          <xsl:variable name="n" select="preceding::tei:cb[1]"/>
          <xsl:value-of select="$n/@n"/>
          <!--
          <a name="{$n}" target="image" href="{$jpg}glossaire/{$n}.jpg" onclick="return colonne(this);">
            <xsl:attribute name="title">
              <xsl:text>Voir l'image de la colonne.</xsl:text>
            </xsl:attribute>
            <xsl:value-of select="$n"/>
          </a>
          -->
          <xsl:text>)</xsl:text>
        </span>
      </xsl:if>
    </div>
  </xsl:template>
  
  <!--  Pour l'instant on ne sait pas ce qu'on fait des images -->
  <xsl:template match="tei:pb | tei:cb"/>

</xsl:transform>
