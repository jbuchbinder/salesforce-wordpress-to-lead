
all: clean zip

clean:
	rm -f *.zip

zip:
	git clone git://github.com/jbuchbinder/salesforce-wordpress-to-lead.git
	rm salesforce-wordpress-to-lead/.git* salesforce-wordpress-to-lead/Makefile -Rf
	zip -r salesforce-wordpress-to-lead.zip salesforce-wordpress-to-lead
	rm -Rf salesforce-wordpress-to-lead

