@touch solr/$(CORPUS).xml
echo "Indexer $$LETTRE, dans SOLR_SERVER=$(SOLR_SERVER)"
LETTRE=$(LETTRE)
if [ "$$LETTRE" = "" ] ; then
	read -p "Choisissez une lettre majuscule ou * pour tout indexer : " LETTRE 
fi

GLOB=src/$$LETTRE.xml
TMP=solr/post
mkdir -p $$TMP
for F in $$GLOB ; do 
	NAME=`basename $$F`
	echo "$$NAME, début de la transformation"
	xsltproc -o "$$TMP/$$NAME" "transform/ducange_solr.xsl" "$$F"
	echo "$$NAME, début de l'indexation"
	curl $(SOLR_SERVER)/update?commit=true --data-binary @$$TMP/$$NAME -H 'Content-type:text/xml; charset=utf-8'
	echo 
done
curl $(SOLR_SERVER)/update --data-binary '<optimize/>' -H 'Content-type:text/xml; charset=utf-8'

