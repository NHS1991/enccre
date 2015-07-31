<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xpath-default-namespace="http://www.tei-c.org/ns/1.0">
    <xsl:output encoding="utf-8" indent="yes"/>
    <xsl:preserve-space elements="p seg emph cell row"/>
    <xsl:template match="/">
        <xsl:apply-templates select="div" mode="pages"/>
    </xsl:template>
    <xsl:template match="div" mode="pages">
        <xsl:apply-templates select="div" mode="page"/>
    </xsl:template>
    <xsl:template match="div" mode="page">
        <div class="page">
            <div class="content">
                <div class="doc_transform">
                   <xsl:apply-templates select="div" mode="article"/>
                </div>
            </div>
            <div class="infos_page">
                <div class="nb_vue">
                    <xsl:value-of select="substring-after(@xml:id,'p')"/>
                </div>
                <div class="nb_page">
                    <xsl:value-of select="@n"/>
                </div>
                <div class="signature_imprimeur">
                    <xsl:value-of select="@signature_imprimeur"/>
                </div>
            </div>
        </div>
    </xsl:template>
    <xsl:template match="div" mode="article">
        <div>
            <xsl:if test="@type">
                <xsl:attribute name="class">
                    <xsl:value-of select="@type"/>
                    <xsl:if test="@subtype">
                        <xsl:value-of select="concat(' ',@subtype)"/>
                    </xsl:if>
                </xsl:attribute>
            </xsl:if>
            <xsl:if test="@xml:id">
                <xsl:attribute name="id">
                    <xsl:value-of select="substring-after(@xml:id,'-')"/>
                </xsl:attribute>
            </xsl:if>
            <xsl:apply-templates select="div" mode="sous_article"/>
        </div>
    </xsl:template>
    <xsl:template match="div" mode="sous_article">
        <div>
            <xsl:if test="@type">
                <xsl:attribute name="class">
                    <xsl:value-of select="@type"/>
                    <xsl:if test="@subtype">
                        <xsl:value-of select="concat(' ',@subtype)"/>
                    </xsl:if>
                </xsl:attribute>
            </xsl:if>
            <xsl:if test="@xml:id">
                <xsl:attribute name="id">
                    <xsl:value-of select="substring-after(@xml:id,'-')"/>
                </xsl:attribute>
            </xsl:if>
            <xsl:apply-templates select="p"/>
        </div>
    </xsl:template>
    <xsl:template match="p">
        <p>
            <xsl:variable name="style">
                <xsl:if test="contains(@rend,'smallcaps')">
                    <xsl:text>font-variant:small-caps;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'center')">
                    <xsl:text>text-align:center;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'italic')">
                    <xsl:text>font-style:italic;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'large')">
                    <xsl:text>font-size:large;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'x-large')">
                    <xsl:text>font-size:x-large;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'xx-large')">
                    <xsl:text>font-size:xx-large;</xsl:text>
                </xsl:if>
                <xsl:if test="@style">
                    <xsl:value-of select="@style"/>
                </xsl:if>
            </xsl:variable>
            <xsl:if test="@n">
                <xsl:attribute name="n">
                    <xsl:value-of select="@n"/>
                </xsl:attribute>
            </xsl:if>
            <xsl:if test="$style!=''">
                <xsl:attribute name="style">
                    <xsl:value-of select="$style"/>
                </xsl:attribute>
            </xsl:if>
            <xsl:apply-templates/>
        </p>
    </xsl:template>
    <xsl:template match="seg">
        <span>
            <xsl:variable name="style">
                <xsl:if test="contains(@rend,'smallcaps')">
                    <xsl:text>font-variant:small-caps;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'center')">
                    <xsl:text>text-align:center;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'italic')">
                    <xsl:text>font-style:italic;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'large')">
                    <xsl:text>font-size:large;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'x-large')">
                    <xsl:text>font-size:x-large;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'xx-large')">
                    <xsl:text>font-size:xx-large;</xsl:text>
                </xsl:if>
                <xsl:if test="@style">
                    <xsl:value-of select="@style"/>
                </xsl:if>
            </xsl:variable>
            <xsl:if test="$style!=''">
                <xsl:attribute name="style">
                    <xsl:value-of select="$style"/>
                </xsl:attribute>
            </xsl:if>
            <xsl:if test="@type">
                <xsl:attribute name="class">
                    <xsl:value-of select="@type"/>
                    <xsl:if test="@subtype">
                        <xsl:value-of select="concat(' ',@subtype)"/>
                    </xsl:if>
                </xsl:attribute>
            </xsl:if>
            <xsl:apply-templates/>
        </span>
    </xsl:template>
    <xsl:template match="ref">
        <a>
          <xsl:attribute name="href">
              <xsl:value-of select="concat('./../../article/',substring-before(substring-after(@target,'v'),'-'),'/',substring-after(@target,'-'))"/>
          </xsl:attribute>
            <xsl:apply-templates/>
        </a>
    </xsl:template>
    <xsl:template match="emph">
        <span>
            <xsl:variable name="style">
                <xsl:if test="contains(@rend,'smallcaps')">
                    <xsl:text>font-variant:small-caps;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'center')">
                    <xsl:text>text-align:center;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'italic')">
                    <xsl:text>font-style:italic;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'large')">
                    <xsl:text>font-size:large;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'x-large')">
                    <xsl:text>font-size:x-large;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'xx-large')">
                    <xsl:text>font-size:xx-large;</xsl:text>
                </xsl:if>
                <xsl:if test="@style">
                    <xsl:value-of select="@style"/>
                </xsl:if>
            </xsl:variable>
            <xsl:if test="$style!=''">
                <xsl:attribute name="style">
                    <xsl:value-of select="$style"/>
                </xsl:attribute>
            </xsl:if>
            <xsl:apply-templates/>
        </span>
    </xsl:template>
    <xsl:template match="table">
        <table>
            <xsl:variable name="style">
                <xsl:if test="contains(@rend,'smallcaps')">
                    <xsl:text>font-variant:small-caps;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'center')">
                    <xsl:text>text-align:center;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'italic')">
                    <xsl:text>font-style:italic;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'large')">
                    <xsl:text>font-size:large;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'x-large')">
                    <xsl:text>font-size:x-large;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'xx-large')">
                    <xsl:text>font-size:xx-large;</xsl:text>
                </xsl:if>
                <xsl:if test="@style">
                    <xsl:value-of select="@style"/>
                </xsl:if>
            </xsl:variable>
            <xsl:if test="$style!=''">
                <xsl:attribute name="style">
                    <xsl:value-of select="$style"/>
                </xsl:attribute>
            </xsl:if>
            <xsl:apply-templates/>
        </table>
    </xsl:template>
    <xsl:template match="row">
        <tr>
            <xsl:variable name="style">
                <xsl:if test="contains(@rend,'smallcaps')">
                    <xsl:text>font-variant:small-caps;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'center')">
                    <xsl:text>text-align:center;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'italic')">
                    <xsl:text>font-style:italic;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'large')">
                    <xsl:text>font-size:large;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'x-large')">
                    <xsl:text>font-size:x-large;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'xx-large')">
                    <xsl:text>font-size:xx-large;</xsl:text>
                </xsl:if>
                <xsl:if test="@style">
                    <xsl:value-of select="@style"/>
                </xsl:if>
            </xsl:variable>
            <xsl:if test="$style!=''">
                <xsl:attribute name="style">
                    <xsl:value-of select="$style"/>
                </xsl:attribute>
            </xsl:if>
            <xsl:apply-templates/>
        </tr>
    </xsl:template>
    <xsl:template match="cell">
        <td>
            <xsl:variable name="style">
                <xsl:if test="contains(@rend,'smallcaps')">
                    <xsl:text>font-variant:small-caps;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'center')">
                    <xsl:text>text-align:center;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'italic')">
                    <xsl:text>font-style:italic;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'large')">
                    <xsl:text>font-size:large;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'x-large')">
                    <xsl:text>font-size:x-large;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'xx-large')">
                    <xsl:text>font-size:xx-large;</xsl:text>
                </xsl:if>
                <xsl:if test="@style">
                    <xsl:value-of select="@style"/>
                </xsl:if>
            </xsl:variable>
            <xsl:if test="$style!=''">
                <xsl:attribute name="style">
                    <xsl:value-of select="$style"/>
                </xsl:attribute>
            </xsl:if>
            <xsl:if test="@rowspan">
                <xsl:attribute name="rowspan">
                    <xsl:value-of select="@rowspan"/>
                </xsl:attribute>
            </xsl:if>
            <xsl:if test="@colspan">
                <xsl:attribute name="colspan">
                    <xsl:value-of select="@colspan"/>
                </xsl:attribute>
            </xsl:if>
            <xsl:apply-templates/>
        </td>
    </xsl:template>
    <xsl:template match="formula">
        $ <xsl:value-of select="."/> $
    </xsl:template>
    <xsl:template match="graphic">
        <img>
            <xsl:variable name="style">
                <xsl:if test="contains(@rend,'smallcaps')">
                    <xsl:text>font-variant:small-caps;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'center')">
                    <xsl:text>text-align:center;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'italic')">
                    <xsl:text>font-style:italic;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'large')">
                    <xsl:text>font-size:large;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'x-large')">
                    <xsl:text>font-size:x-large;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'xx-large')">
                    <xsl:text>font-size:xx-large;</xsl:text>
                </xsl:if>
                <xsl:if test="@style">
                    <xsl:value-of select="@style"/>
                </xsl:if>
            </xsl:variable>
            <xsl:if test="$style!=''">
                <xsl:attribute name="style">
                    <xsl:value-of select="$style"/>
                </xsl:attribute>
            </xsl:if>
            <xsl:if test="@width">
                <xsl:attribute name="width">
                    <xsl:value-of select="@width"/>
                </xsl:attribute>
            </xsl:if>
            <xsl:attribute name="src">
                <xsl:value-of select="@url"/>
            </xsl:attribute>
        </img>
    </xsl:template>
    <xsl:template match="lg">
        <ul>
            <xsl:variable name="style">
                <xsl:if test="contains(@rend,'smallcaps')">
                    <xsl:text>font-variant:small-caps;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'center')">
                    <xsl:text>text-align:center;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'italic')">
                    <xsl:text>font-style:italic;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'large')">
                    <xsl:text>font-size:large;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'x-large')">
                    <xsl:text>font-size:x-large;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'xx-large')">
                    <xsl:text>font-size:xx-large;</xsl:text>
                </xsl:if>
                <xsl:if test="@style">
                    <xsl:value-of select="@style"/>
                </xsl:if>
            </xsl:variable>
            <xsl:if test="$style!=''">
                <xsl:attribute name="style">
                    <xsl:value-of select="$style"/>
                </xsl:attribute>
            </xsl:if>
            <xsl:apply-templates/>
        </ul>
    </xsl:template>
    <xsl:template match="l">
        <li>
            <xsl:variable name="style">
                <xsl:if test="contains(@rend,'smallcaps')">
                    <xsl:text>font-variant:small-caps;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'center')">
                    <xsl:text>text-align:center;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'italic')">
                    <xsl:text>font-style:italic;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'large')">
                    <xsl:text>font-size:large;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'x-large')">
                    <xsl:text>font-size:x-large;</xsl:text>
                </xsl:if>
                <xsl:if test="contains(@rend,'xx-large')">
                    <xsl:text>font-size:xx-large;</xsl:text>
                </xsl:if>
                <xsl:if test="@style">
                    <xsl:value-of select="@style"/>
                </xsl:if>
            </xsl:variable>
            <xsl:if test="$style!=''">
                <xsl:attribute name="style">
                    <xsl:value-of select="$style"/>
                </xsl:attribute>
            </xsl:if>
            <xsl:apply-templates/>
        </li>
    </xsl:template>
    <xsl:template match="lb">
        <br/>
    </xsl:template>
    <xsl:template match="cb">
        <span>
            <xsl:attribute name="style">
                <xsl:text>font-weight:bold;</xsl:text>
            </xsl:attribute>
            [saut de colonne]
        </span>
    </xsl:template>
    <xsl:template match="hr">
        <hr />
    </xsl:template>
   <xsl:template match="anchor">
        <span style="font-weight:bold;">
            <xsl:if test="@type='debut_deux_colonnes'">
                [debut de mode de deux colonnes]
            </xsl:if>
            <xsl:if test="@type='fin_deux_colonnes'">
                [fin de mode de deux colonnes]
            </xsl:if>
        </span>
    </xsl:template>
    <xsl:template match="foreign">
        <span>
            <xsl:attribute name="lang">
                <xsl:value-of select="@xml:lang"/>
            </xsl:attribute>
            <xsl:apply-templates/>
        </span>
    </xsl:template>
    <xsl:template match="choice">
        <span>
            <xsl:attribute name="class">
                <xsl:value-of select="@type"/>
            </xsl:attribute>
            [<xsl:apply-templates/>]
        </span>
    </xsl:template>
    <xsl:template match="sic">
        <span>
            <xsl:apply-templates/>
        </span>
    </xsl:template>
    <xsl:template match="corr">
        <span style="font-weight:bold">
            <xsl:text> </xsl:text><xsl:apply-templates/>
        </span>
    </xsl:template>
    <!--<xsl:character-map name="no-control-characters">-->
        <!--<xsl:output-character character="&#127;" string=" "/>-->
        <!--<xsl:output-character character="&#128;" string=" "/>-->
        <!--<xsl:output-character character="&#129;" string=" "/>-->
        <!--<xsl:output-character character="&#130;" string=" "/>-->
        <!--<xsl:output-character character="&#131;" string=" "/>-->
        <!--<xsl:output-character character="&#132;" string=" "/>-->
        <!--<xsl:output-character character="&#133;" string=" "/>-->
        <!--<xsl:output-character character="&#134;" string=" "/>-->
        <!--<xsl:output-character character="&#135;" string=" "/>-->
        <!--<xsl:output-character character="&#136;" string=" "/>-->
        <!--<xsl:output-character character="&#137;" string=" "/>-->
        <!--<xsl:output-character character="&#138;" string=" "/>-->
        <!--<xsl:output-character character="&#139;" string=" "/>-->
        <!--<xsl:output-character character="&#140;" string=" "/>-->
        <!--<xsl:output-character character="&#141;" string=" "/>-->
        <!--<xsl:output-character character="&#142;" string=" "/>-->
        <!--<xsl:output-character character="&#143;" string=" "/>-->
        <!--<xsl:output-character character="&#144;" string=" "/>-->
        <!--<xsl:output-character character="&#145;" string=" "/>-->
        <!--<xsl:output-character character="&#146;" string=" "/>-->
        <!--<xsl:output-character character="&#147;" string=" "/>-->
        <!--<xsl:output-character character="&#148;" string=" "/>-->
        <!--<xsl:output-character character="&#149;" string=" "/>-->
        <!--<xsl:output-character character="&#150;" string=" "/>-->
        <!--<xsl:output-character character="&#151;" string=" "/>-->
        <!--<xsl:output-character character="&#152;" string=" "/>-->
        <!--<xsl:output-character character="&#153;" string=" "/>-->
        <!--<xsl:output-character character="&#154;" string=" "/>-->
        <!--<xsl:output-character character="&#155;" string=" "/>-->
        <!--<xsl:output-character character="&#156;" string=" "/>-->
        <!--<xsl:output-character character="&#157;" string=" "/>-->
        <!--<xsl:output-character character="&#158;" string=" "/>-->
        <!--<xsl:output-character character="&#159;" string=" "/>-->
    <!--</xsl:character-map>-->
</xsl:stylesheet>