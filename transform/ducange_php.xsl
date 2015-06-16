<?xml version="1.0" encoding="UTF-8"?>
<!--
Document idexable sous forme d'un tableau CSV
séparateur de ligne : \n
séparateur de champ : \t

2012-04 : charger les articles dans une base sqlite avec un pilote php
2009-12 : Nouvelle version juste pour un suggest
2008-09 : débranchement de la simplification des graphies à l'indexation
2007-10 : analyseur sur les champs texte, citations, gloses, comme fonction php
          chercher les php:function('xsl_analyse')

-->
<xsl:transform version="1.1"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:tei="http://www.tei-c.org/ns/1.0"

  xmlns:exslt="http://exslt.org/common"
  xmlns:php="http://php.net/xsl"
  extension-element-prefixes="exslt php"
>
  <!-- transformation des articles html -->
  <xsl:import href="ducange_html.xsl"/>
  <xsl:output encoding="UTF-8" indent="yes" />


  <xsl:output  indent="yes" method="text" encoding="UTF-8" omit-xml-declaration="yes"/>
  <!-- la lettre, en espérant que ce soit bien le premier caractère  -->
  <xsl:param name="lettre" select="substring(//tei:form[@rend='b'], 1, 1)"/>
  <xsl:variable name="apos">'</xsl:variable>
  <xsl:variable name="quot">"</xsl:variable>
  <!-- séparateur de ligne -->
  <xsl:variable name="LF" select="'&#10;'"/>
  <!-- séparateur de champ -->
  <xsl:variable name="TAB" select="'&#9;'"/>
  <!-- pour échappement sql (attention ce ne sont que des espace -->
  <xsl:variable name="sql-from" select="'&#13;&#10;&#9;'"/>
  <xsl:variable name="sql-to" select="'   '"/>
  <!-- mode=type de sortie -->

  <xsl:template match="/">
    <sqlite>
      <xsl:apply-templates select="/tei:TEI/tei:text/tei:body/tei:entry"/>
    </sqlite>
  </xsl:template>

  <!-- écriture des liens, article ou sous-article -->
  <xsl:template match="@target">
    <xsl:attribute name="href">
      <xsl:choose>
        <xsl:when test="substring-before(., '-') != ''">
          <xsl:value-of select="substring-before(., '-')"/>
          <xsl:text>#</xsl:text>
          <xsl:value-of select="."/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="."/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:attribute>
  </xsl:template>
  
  <!-- article avec doute sur l'homographe résolu par le serveur -->
  <xsl:template match="@cRef">
    <xsl:attribute name="href">
      <xsl:value-of select="."/>
    </xsl:attribute>
  </xsl:template>


  <!-- Un article à envoyer pour indexation 
  id, label, image, html, meta, fro?
  -->
  <xsl:template match="tei:entry">
    <xsl:variable name="id" select="string(@xml:id)"/>
    <xsl:variable name="label">
      <xsl:call-template name="label"/>
    </xsl:variable>
    <xsl:variable name="head">
      <title>
        <xsl:value-of select="$label"/>
        <xsl:text>, Glossarium mediae et infimae latinitatis, Du Cange et al.</xsl:text>
      </title>
      <meta name="label">
        <xsl:attribute name="content">
          <xsl:value-of select="substring($label, 1, 1)"/>
          <xsl:value-of select="translate(substring($label, 2), $caps, $mins)"/>
        </xsl:attribute>
      </meta>
      <link rel="dc.isPartOf" href="glossarium"/>
      <meta name="dc.language" content="lat"/>
      <!-- utilisé pour savoir si un article a de l'ancien français -->
      <xsl:if test=".//tei:quote[@xml:lang = 'fro']">
        <meta name="dc.language" content="fro"/>
      </xsl:if>
    </xsl:variable>
    <xsl:variable name="body">
      <xsl:call-template name="entry"/>
    </xsl:variable>
    <xsl:variable name="cb">
      <xsl:for-each select="preceding::tei:cb[1]">
        <xsl:value-of select="@n"/>
      </xsl:for-each>
    </xsl:variable>
    <xsl:value-of select="php:function('Ducange::entry', $id, string($cb), string($label), exslt:node-set($body), exslt:node-set($head))"/>
    <xsl:for-each select=".//tei:form">
      <xsl:variable name="dictScrap" select="ancestor::tei:dictScrap[1]"/>
      <xsl:variable name="anchor">
        <xsl:choose>
          <!-- no need of anchor for first block -->
          <xsl:when test="count(ancestor::tei:entry/tei:dictScrap[1]|$dictScrap) = 1"/>
          <xsl:otherwise>
            <xsl:text>#</xsl:text>
            <xsl:value-of select="$dictScrap/@xml:id"/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:variable>
      <xsl:value-of select="php:function('Ducange::form', string(@rend), string(.), $id, $anchor)"/>
    </xsl:for-each>
  </xsl:template>



  <!-- lien aux images pour le site PHP -->
  <xsl:template match="tei:cb" name="cb">
    <a name="{@n}" target="image" href="{$jpg}{$lettre}/{@n}.jpg" class="{local-name()}" onclick="return colonne(this);">
      <xsl:attribute name="title">
        <xsl:text>Voir l'image, tome </xsl:text>
        <xsl:value-of select="$tome"/>
        <xsl:text>, page </xsl:text>
        <xsl:value-of select="substring(@n, 1, 3)"/>
        <xsl:text>, colonne </xsl:text>
        <xsl:value-of select="substring(@n, 4)"/>
        <xsl:text>.</xsl:text>
      </xsl:attribute>
      <img src="img/image.png" alt="[]"/>
    </a>
  </xsl:template>


  <!-- construction d'un document pour indexation
id,adresse,lemme,stem,texte,citations,gloses
  -->
  <xsl:template match="VIEUX" mode="sql">
    <!-- id -->
    <xsl:value-of select="@xml:id"/>
    <!-- adresse, telle que dans l'article, avec no d'ordre après -->
    <xsl:value-of select="$TAB"/>
    <xsl:for-each select="dictScrap[1]/form/*[name() != 'num']">
      <xsl:value-of select="normalize-space(.)"/>
      <xsl:if test="position() != last()">
        <xsl:text> </xsl:text>
      </xsl:if>
    </xsl:for-each>
    <xsl:if test="dictScrap[1]/form/num">
      <xsl:text> </xsl:text>
      <xsl:value-of select="normalize-space(dictScrap[1]/form/num)"/>
    </xsl:if>
    <!-- lemme, en minuscules, sans numéro d'ordre, sans expression -->
    <xsl:value-of select="$TAB"/>
    <xsl:variable name="vedette">
      <xsl:value-of select="
  translate(normalize-space(dictScrap[1]/form/b), $caps, $mins)
    "/>
    </xsl:variable>
    <xsl:value-of select="$vedette"/>
    <!-- stem, vedette cherchable (flottement des graphies) -->
    <xsl:value-of select="$TAB"/>
    <xsl:value-of select="php:function('xsl_analyse', string($vedette))"/>
    <!--
    <xsl:value-of select="$TAB"/>
    <xsl:for-each select="dictScrap/form[not(@type='lemma')]">
      <xsl:apply-templates/>
      <xsl:if test="position() != last()">, </xsl:if>
    </xsl:for-each>
    -->
    <!-- texte -->
    <xsl:value-of select="$TAB"/>
    <!-- xsl:text>"</xsl:text -->
    <xsl:for-each select="dictScrap">
      <!--
      2008-09-16, Alain Guerreau, recherche floue = bruit
      <xsl:value-of select="php:function('xsl_analyse', string(.))"/>
      -->
      <!-- normalement les guillements devraient nous protéger des problèmes de sauts de ligne ou de tabulation -->
      <xsl:value-of select="translate(., concat($TAB, $LF, $quot), '  ')"/>
    </xsl:for-each>
    <!-- xsl:text>"</xsl:text -->
    <!-- citations -->
    <xsl:value-of select="$TAB"/>
    <!-- xsl:text>"</xsl:text -->
    <xsl:for-each select=".//quote">
      <!--
      2008-09-16, Alain Guerreau, recherche floue = bruit
      <xsl:value-of select="php:function('xsl_analyse', string(.))"/>
      -->
      <!-- normalement les guillements devraient nous protéger des problèmes de sauts de ligne ou de tabulation -->
      <xsl:value-of select="translate(., concat($TAB, $LF, $quot), '  ')"/>
    </xsl:for-each>
    <!-- xsl:text>"</xsl:text -->
    <!-- les gloses ne sont pour l'instant pas distinguées dans la source XML,
car elles contiennent des référence bibliographiques destinées à être rattachée aux citations -->
    <xsl:value-of select="$TAB"/>
    <!-- xsl:text>"</xsl:text -->
    <xsl:apply-templates select="dictScrap" mode="glose"/>
    <!-- xsl:text>"</xsl:text -->
    <xsl:value-of select="$LF"/>
  </xsl:template>


  <!-- formatage pour mode glose, tout sauf les citations -->
  <!-- désamorcer les saut de ligne de la source XML -->
  <xsl:template match="entry/text()" mode="glose"/>
  <!-- saut de ligne à chaque paragraphe -->
  <xsl:template match="dictScrap[position() &gt; 1]" mode="glose">
    <xsl:text>\n\n</xsl:text>
    <xsl:apply-templates mode="glose"/>
  </xsl:template>
  <!-- passer à travers les noeuds -->
  <xsl:template match="*" mode="glose">
    <xsl:apply-templates mode="glose"/>
  </xsl:template>
  <!-- pas de citation -->
  <xsl:template match="quote" mode="glose"/>
  <!-- échappements SQL du texte -->
  <xsl:template match="text()" mode="glose">
    <!--
    2008-09-16, Alain Guerreau, recherche floue = bruit
    <xsl:value-of select="php:function('xsl_analyse', string(.))"/>
    -->
    <!-- normalement les guillements devraient nous protéger des problèmes de sauts de ligne ou de tabulation -->
    <xsl:value-of select="translate(., concat($TAB, $LF, $quot), '  ')"/>
  </xsl:template>



</xsl:transform>
