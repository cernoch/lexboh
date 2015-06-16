<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.1" 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:tei="http://www.tei-c.org/ns/1.0"
  exclude-result-prefixes="tei"
>
  <!-- Pas de notes, couper ici -->
  <xsl:template name="notes"/>
  <xsl:param name="corpus">ducange</xsl:param>
  <!-- date de génération  -->
  <xsl:param name="date"/>
  <!-- lien absolu vers les images -->
  <xsl:param name="jpg">http://media.enc.sorbonne.fr/ducange/jpg/</xsl:param>
  <!-- la lettre, en espérant que ce soit bien le premier caractère  -->
  <xsl:param name="lettre" select="substring(//tei:form[@rend='b'], 1, 1)"/>
  
  <!-- Majuscules, pour conversions. -->
  <xsl:variable name="caps">ABCDEFGHIJKLMNOPQRSTUVWXYZÆŒÇÀÁÂÃÄÅÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝ.</xsl:variable>
  <!-- Minuscules, pour conversions -->
  <xsl:variable name="mins">abcdefghijklmnopqrstuvwxyzæœçàáâãäåèéêëìíîïòóôõöùúûüý</xsl:variable>
  <!-- pour traduire une lettre en tome -->
  <xsl:variable name="tome">
    <xsl:choose>
      <xsl:when test="$lettre = 'A'">1</xsl:when>
      <xsl:when test="$lettre = 'B'">1</xsl:when>
      <xsl:when test="$lettre = 'C'">2</xsl:when>
      <xsl:when test="$lettre = 'D'">3</xsl:when>
      <xsl:when test="$lettre = 'E'">3</xsl:when>
      <xsl:when test="$lettre = 'F'">3</xsl:when>
      <xsl:when test="$lettre = 'G'">4</xsl:when>
      <xsl:when test="$lettre = 'H'">4</xsl:when>
      <xsl:when test="$lettre = 'I'">4</xsl:when>
      <xsl:when test="$lettre = 'J'">4</xsl:when>
      <xsl:when test="$lettre = 'K'">4</xsl:when>
      <xsl:when test="$lettre = 'L'">5</xsl:when>
      <xsl:when test="$lettre = 'M'">5</xsl:when>
      <xsl:when test="$lettre = 'N'">5</xsl:when>
      <xsl:when test="$lettre = 'O'">6</xsl:when>
      <xsl:when test="$lettre = 'P'">6</xsl:when>
      <xsl:when test="$lettre = 'Q'">6</xsl:when>
      <xsl:when test="$lettre = 'R'">7</xsl:when>
      <xsl:when test="$lettre = 'S'">7</xsl:when>
      <xsl:when test="$lettre = 'T'">8</xsl:when>
      <xsl:when test="$lettre = 'U'">8</xsl:when>
      <xsl:when test="$lettre = 'V'">8</xsl:when>
      <xsl:when test="$lettre = 'W'">8</xsl:when>
      <xsl:when test="$lettre = 'X'">8</xsl:when>
      <xsl:when test="$lettre = 'Y'">8</xsl:when>
      <xsl:when test="$lettre = 'Z'">8</xsl:when>
    </xsl:choose>
  </xsl:variable>

  <xsl:template match="tei:teiHeader"/>
  <xsl:template match="tei:TEI">
    <xsl:text disable-output-escaping="yes">&lt;!DOCTYPE html&gt;</xsl:text>
    <html>
      <head>
        <meta charset="UTF-8"/>
        <link rel="stylesheet" href="../enc/ducange.css"/>
      </head>
      <body>
        <xsl:apply-templates select="*"/>
      </body>
    </html>
  </xsl:template>
  <xsl:template match="tei:text | tei:body">
    <xsl:apply-templates/>
  </xsl:template>
  <xsl:template match="tei:quote">
    <blockquote class="quote {@xml:lang}">
      <xsl:apply-templates/>
    </blockquote>
  </xsl:template>
  <xsl:template match="tei:l">
    <div class="l">
      <xsl:apply-templates/>
    </div>
  </xsl:template>
  <xsl:template match="tei:num">
    <span class="num">
      <xsl:apply-templates/>
    </span>
  </xsl:template>
  <xsl:template match="tei:hi[@rend='sup']">
    <sup>
      <xsl:apply-templates/>
    </sup>
  </xsl:template>
  <xsl:template match="tei:hi[@rend='i']">
    <i>
      <xsl:apply-templates/>
    </i>
  </xsl:template>
  <xsl:template match="tei:abbr">
    <abbr>
      <xsl:apply-templates/>
    </abbr>
  </xsl:template>
  <xsl:template match="tei:emph">
    <em>
      <xsl:apply-templates/>
    </em>
  </xsl:template>
  <xsl:template match="tei:foreign">
    <i class="foreign {@xml:lang}">
      <xsl:apply-templates/>
    </i>
  </xsl:template>
  <xsl:template match="tei:mentioned | tei:title">
    <em class="{local-name()}">
      <xsl:apply-templates/>
    </em>
  </xsl:template>
  <xsl:template match="tei:p">
    <p>
      <xsl:apply-templates/>
    </p>
  </xsl:template>
  <xsl:template match="tei:list">
    <ul>
      <xsl:apply-templates/>
    </ul>
  </xsl:template>
  <xsl:template match="tei:item">
    <li>
      <xsl:apply-templates/>
    </li>
  </xsl:template>
  <xsl:template match="tei:name | tei:bibl">
    <span class="{local-name()}">
      <xsl:apply-templates/>
    </span>
  </xsl:template>
  <xsl:template match="tei:persName">
    <span class="{@type}">
      <xsl:apply-templates/>
    </span>
  </xsl:template>
  <xsl:template match="tei:ref">
    <a>
      <xsl:apply-templates select="@*"/>
      <xsl:apply-templates/>
    </a>
  </xsl:template>
  <xsl:template match="@target">
    <xsl:attribute name="href">
      <xsl:choose>
        <!-- Dans le glossaire ? -->
        <xsl:when test="substring(., 1, 1)='#'">
          <xsl:value-of select="."/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="substring(., 1, 1)"/>
          <xsl:text>.xml#</xsl:text>
          <xsl:value-of select="."/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:attribute>
  </xsl:template>
  <xsl:template match="@cRef">
    <xsl:attribute name="href">
      <xsl:value-of select="substring(., 1, 1)"/>
      <xsl:text>.xml#</xsl:text>
      <xsl:value-of select="."/>
    </xsl:attribute>
  </xsl:template>
  <!-- Un article -->
  <xsl:template name="entry" match="tei:entry">
    <section class="entry" id="{@xml:id}">
      <xsl:call-template name="entry_bibl"/>
      <div class="text">
        <xsl:apply-templates/>
      </div>
      <xsl:call-template name="entry_rights"/>
    </section>
  </xsl:template>

  <!-- alinéas, TODO sous-vedette en form ?  -->
  <xsl:template match="tei:dictScrap">
    <!-- id, surtout pour les alinéas avec sous-vedette -->
    <xsl:variable name="id">
      <xsl:value-of select="@xml:id"/>
    </xsl:variable>
    <!-- tokens pour extractions ultérieures (toujours utile ?) <xsl:comment>{</xsl:comment> -->
    
    <!-- ancre de sous-vedette ? -->
    <xsl:if test="tei:form[@rend='sc']"/>
    <!--
    <a class="anchor" name="{$id}">
      <xsl:text>&#160;</xsl:text>
    </a>
    -->
    <div>
      <xsl:attribute name="class">
        <xsl:text>dictScrap</xsl:text>
        <xsl:choose>
          <!-- premier alinéa -->
          <xsl:when test="tei:form[@rend='b']"> p1</xsl:when>
          <!-- avec sous-vedette -->
          <xsl:when test="tei:form[@rend='sc']"> re</xsl:when>
          <!-- défaut -->
        </xsl:choose>
        <xsl:if test=".//tei:quote[@xml:lang='fro']"> p_fro</xsl:if>
      </xsl:attribute>
      <xsl:attribute name="id">
        <xsl:value-of select="$id"/>
      </xsl:attribute>
      <xsl:if test="@rend">
        <div class="dictScrap_bibl">
          <span>
            <xsl:call-template name="author"/>
            <xsl:text>, </xsl:text>
            <xsl:call-template name="date"/>
            <xsl:text>.</xsl:text>
          </span>
        </div>
      </xsl:if>
      <!-- bouton d'édition -->
      <!--
      <xsl:if test="$edit">
        <a href="#{$id}" class="but" onclick="return edit('{$id}')">✎</a>
      </xsl:if>
      -->
      <!-- puce -->
      <xsl:choose>
        <!-- pas de puce au premier alinéa -->
        <xsl:when test="tei:form[@rend='b']"/>
        <!-- numéro de sous-vedette ? -->
        <xsl:when test="tei:form[@rend='sc']">
          <!--
          <a href="#{$id}" class="count">
            <xsl:call-template name="num-dictScrap"/>
          </a>
          <xsl:text> </xsl:text>
          -->
        </xsl:when>
        <!-- il y a une citation en début de bloc -->
        <xsl:when test="contains(' quote cit ', concat(' ', local-name(node()[1][normalize-space(.) != '']), ' '))"/>
        <!-- carreau -->
        <xsl:otherwise>
          <tt class="square">
            <xsl:text>◊</xsl:text>
          </tt>
          <xsl:text> </xsl:text>
        </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates/>
    </div>
    <!-- <xsl:comment>}</xsl:comment> -->
  </xsl:template>

  <!-- vedette -->
  <xsl:template match="tei:form">
    <a>
      <xsl:choose>
        <xsl:when test="@rend='b'">
          <xsl:attribute name="href">
            <xsl:value-of select="../../@xml:id"/>
          </xsl:attribute>
        </xsl:when>
        <xsl:when test="count(.|../tei:form[1])=1">
          <xsl:attribute name="href">
            <xsl:value-of select="../../@xml:id"/>
            <xsl:text>#</xsl:text>
            <xsl:value-of select="../@xml:id"/>
          </xsl:attribute>
        </xsl:when>
      </xsl:choose>
      <xsl:attribute name="class">
        <xsl:text>form </xsl:text>
        <xsl:value-of select="@rend"/>
      </xsl:attribute>
      <xsl:apply-templates/>
    </a>
  </xsl:template>

  <xsl:template match="tei:form/tei:seg">
    <span class="seg">
      <xsl:apply-templates/>
    </span>
  </xsl:template>

  <!-- entre citation et référence, un élément un peu vide -->
  <xsl:template match="tei:lbl" >
    <span class="lbl {@type}">
      <xsl:apply-templates/>
    </span>
  </xsl:template>

  <!-- les ajouts ne sont pas balisés, pour éviter des chevauchement dans les extractions de citations et gloses -->
  <xsl:template match="tei:add">
    <!--
    <span>
      <xsl:text>[</xsl:text>
      <b>
        <xsl:value-of select="@resp"/>
      </b>
      <xsl:text> </xsl:text>
      <xsl:apply-templates/>
      <xsl:text>]</xsl:text>
    </span>
    -->
    <xsl:apply-templates/>
  </xsl:template>

  <!-- rappel de la vedette -->
  <xsl:template match="tei:oVar">
    <em class="oVar">
      <xsl:apply-templates/>
    </em>
  </xsl:template>

  <!-- citation de glossaire, note de lexicographe dans une citation -->
  <xsl:template match="tei:gloss | tei:note">
    <q class="{local-name()}">
      <xsl:apply-templates/>
    </q>
  </xsl:template>
  <!-- pas de note en bas de page -->
  <xsl:template match="node()" mode="fn"/>

  <xsl:template name="entry_bibl">
    <!-- référence biblio complète

« Quadirigena» (par P. Carpentier, 1766), dans Charles Du Fresne, sieur du Cange, et al., Glossarium mediae et infimae latinitatis, éd. augm., Niort: L. Favre, 1883‑1887, t. 6, p. 581b. (ou col. 581b?).
    -->
    <div class="entry_bibl">
        <xsl:for-each select="preceding::tei:cb[1]">
          <xsl:call-template name="cb"/>
        </xsl:for-each>
        <xsl:variable name="nb" select="preceding::tei:cb[1]/@n"/>
        <xsl:text>« </xsl:text>
          <b>
            <xsl:variable name="form" select="normalize-space(tei:dictScrap[1]/tei:form[1])"/>
            <xsl:value-of select="substring($form, 1, 1)"/>
            <xsl:value-of select="translate(substring($form, 2), $caps, $mins)"/>
          </b>
        <xsl:text> » (par </xsl:text>
        <xsl:call-template name="author"/>
        <xsl:text>, </xsl:text>
        <xsl:call-template name="date"/>
        <xsl:text>), dans </xsl:text>
        <span class="author">du Cange</span>
        <xsl:text>, </xsl:text>
        <i>et al.</i>
        <xsl:text>, </xsl:text>
        <i class="title">Glossarium mediae et infimae latinitatis</i>
        <xsl:text>, éd. augm., Niort : L. Favre, 1883‑1887</xsl:text>
        <xsl:text>, t. </xsl:text>
        <xsl:value-of select="$tome"/>
        <xsl:text>, col. </xsl:text>
        <xsl:value-of select="$nb"/>
        <xsl:text>. </xsl:text>
        <!-- URI de référence -->
        <xsl:variable name="URI">http://ducange.enc.sorbonne.fr/<xsl:value-of select="@xml:id"/></xsl:variable>
        <!-- xsl:text>&lt;</xsl:text -->
        <a href="{$URI}" target="_top"><xsl:value-of select="$URI"/></a>
    </div>
  </xsl:template>

  <!-- rédacteurs
  ducange|benedictin|carpenter|didot|favre|enc
  -->
  <xsl:template name="author">
    <xsl:param name="resp" select="@rend"/>
    <xsl:choose>
      <xsl:when test="$resp = 'ducange'">
        <xsl:text>C.&#160;</xsl:text>
        <span class="author">du&#160;Cange</span>
      </xsl:when>
      <xsl:when test="starts-with( $resp, 'benedictin') ">
        <xsl:text>les Bénédictins&#160;de&#160;St.&#160;Maur</xsl:text>
      </xsl:when>
      <xsl:when test="$resp = 'carpentier'">
        <xsl:text>P.&#160;</xsl:text>
        <span class="author">Carpentier</span>
      </xsl:when>
      <xsl:when test="$resp = 'didot' or $resp= 'henschel'">
        <xsl:text>L.&#160;</xsl:text>
        <span class="author">Henschel</span>
      </xsl:when>
      <xsl:when test="$resp ='favre'">
        <xsl:text>L.&#160;</xsl:text>
        <span class="author">Favre</span>
      </xsl:when>
      <xsl:when test="$resp ='enc'">
        <xsl:text>École&#160;nationale&#160;des&#160;chartes</xsl:text>
      </xsl:when>
    </xsl:choose>
  </xsl:template>
  
    <!-- numéro de page, avec icone pour voir l'image -->
  <xsl:template match="tei:cb" name="cb">
      <xsl:variable name="cb" select="."/>
      <xsl:variable name="n" select="$cb/@n"/>
      <!--
      <xsl:if test="$precedent">
        <a class="prec" href="#{$precedent/@n}" title="Colonne précédente.">&lt;</a>
      </xsl:if>
      -->

    <a name="{$n}" target="image" href="{$jpg}{$lettre}/{$n}.jpg" class="{local-name()}" onclick="return colonne(this);">
      <xsl:attribute name="title">
        <xsl:text>Voir l'image, tome </xsl:text>
        <xsl:value-of select="$tome"/>
        <xsl:text>, page </xsl:text>
        <xsl:value-of select="substring($n, 1, 3)"/>
        <xsl:text>, colonne </xsl:text>
        <xsl:value-of select="substring($n, 4)"/>
        <xsl:text>.</xsl:text>
      </xsl:attribute>
      <img src="img/image.png" alt="[]"/>
    </a>

      <!--
      <xsl:variable name="suivant" select="following::cb[1]"/>
      <a class="suiv">
      <xsl:if test="$suivant">
        <xsl:attribute name="title">Colonne suivante</xsl:attribute>
        <xsl:attribute name="href">#<xsl:value-of select="$suivant/@n"/></xsl:attribute>
      </xsl:if>
      <xsl:text>&gt;</xsl:text></a>
      -->
  </xsl:template>
  <!--  saut de page, information déjà dans le saut de colonne -->
  <xsl:template match="tei:pb"/>
  <xsl:template match="tei:figure">
    <i class="figure">
      <xsl:text>(illustration </xsl:text>
      <xsl:call-template name="cb">
        <xsl:with-param name="cb" select="preceding::tei:cb[1]"/>
      </xsl:call-template>
      <xsl:text>)</xsl:text>
    </i>
  </xsl:template>


  <!-- date selon responsable -->
  <xsl:template name="date">
    <xsl:param name="resp" select="@rend"/>
    <xsl:choose>
      <xsl:when test="$resp = 'ducange'">1678</xsl:when>
      <xsl:when test="starts-with($resp, 'benedictin') ">1733–1736</xsl:when>
      <xsl:when test="$resp = 'carpentier'">1766</xsl:when>
      <xsl:when test="$resp = 'didot' or $resp= 'henschel'">1840–1850</xsl:when>
      <xsl:when test="$resp ='favre'">1883–1887</xsl:when>
      <xsl:when test="$resp ='enc'">2008-...</xsl:when>
    </xsl:choose>
  </xsl:template>

  <xsl:template name="entry_rights">
    <div class="rights">
      <xsl:for-each select="preceding-sibling::tei:entry[1]">
        <a rel="prev" class="prev" href="{@xml:id}">
          <xsl:value-of select="tei:dictScrap[1]/tei:form[1]"/>
        </a>
      </xsl:for-each>
      <xsl:for-each select="following-sibling::tei:entry[1]">
        <a rel="next" class="next" href="{@xml:id}">
          <xsl:value-of select="tei:dictScrap[1]/tei:form[1]"/>
        </a>
      </xsl:for-each>
      <span class="rights">©&#160;<a href="http://www.enc.sorbonne.fr/" target="_top">École&#160;des&#160;chartes</a>
    <xsl:if test="$date"><span class="timestamp"> (mise à jour  <xsl:value-of select="$date"/>)</span></xsl:if>.</span>
    </div>
  </xsl:template>

  <!-- Titre court d'une entrée -->
  <xsl:template name="label" match="tei:entry" mode="label">
    <xsl:variable name="form">
      <xsl:for-each select="tei:dictScrap[1]/tei:form[@rend='b']">
        <xsl:apply-templates select="text()"/>
        <xsl:if test="tei:num">
          <xsl:text> </xsl:text>
          <xsl:value-of select="tei:num"/>
        </xsl:if>
      </xsl:for-each>
    </xsl:variable>
    <xsl:value-of select="translate(normalize-space($form), '.', '')"/>
  </xsl:template>

  <!-- <*>, modèle par défaut d'interception des éléments non pris en charge -->
  <xsl:template match="*">
    <div>
      <xsl:call-template name="tag"/>
      <xsl:apply-templates/>
      <b style="color:red">
        <xsl:text>&lt;/</xsl:text>
        <xsl:value-of select="name()"/>
        <xsl:text>&gt;</xsl:text>
      </b>
    </div>
  </xsl:template>

  <!-- Utile au déboguage, affichage de l'élément en cours -->
  <xsl:template name="tag">
    <b style="color:red">
      <xsl:text>&lt;</xsl:text>
      <xsl:value-of select="name()"/>
      <xsl:for-each select="@*">
        <xsl:text> </xsl:text>
        <xsl:value-of select="name()"/>
        <xsl:text>="</xsl:text>
        <xsl:value-of select="."/>
        <xsl:text>"</xsl:text>
      </xsl:for-each>
      <xsl:text>&gt;</xsl:text>
    </b>
  </xsl:template>


</xsl:transform>
