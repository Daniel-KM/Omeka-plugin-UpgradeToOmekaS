-- Exported from OmekaS v1.1.1

SET foreign_key_checks = 0;

TRUNCATE `property`;
INSERT INTO `property` (`id`, `owner_id`, `vocabulary_id`, `local_name`, `label`, `comment`) VALUES
(1,	NULL,	1,	'title',	'Title',	'A name given to the resource.'),
(2,	NULL,	1,	'creator',	'Creator',	'An entity primarily responsible for making the resource.'),
(3,	NULL,	1,	'subject',	'Subject',	'The topic of the resource.'),
(4,	NULL,	1,	'description',	'Description',	'An account of the resource.'),
(5,	NULL,	1,	'publisher',	'Publisher',	'An entity responsible for making the resource available.'),
(6,	NULL,	1,	'contributor',	'Contributor',	'An entity responsible for making contributions to the resource.'),
(7,	NULL,	1,	'date',	'Date',	'A point or period of time associated with an event in the lifecycle of the resource.'),
(8,	NULL,	1,	'type',	'Type',	'The nature or genre of the resource.'),
(9,	NULL,	1,	'format',	'Format',	'The file format, physical medium, or dimensions of the resource.'),
(10,	NULL,	1,	'identifier',	'Identifier',	'An unambiguous reference to the resource within a given context.'),
(11,	NULL,	1,	'source',	'Source',	'A related resource from which the described resource is derived.'),
(12,	NULL,	1,	'language',	'Language',	'A language of the resource.'),
(13,	NULL,	1,	'relation',	'Relation',	'A related resource.'),
(14,	NULL,	1,	'coverage',	'Coverage',	'The spatial or temporal topic of the resource, the spatial applicability of the resource, or the jurisdiction under which the resource is relevant.'),
(15,	NULL,	1,	'rights',	'Rights',	'Information about rights held in and over the resource.'),
(16,	NULL,	1,	'audience',	'Audience',	'A class of entity for whom the resource is intended or useful.'),
(17,	NULL,	1,	'alternative',	'Alternative Title',	'An alternative name for the resource.'),
(18,	NULL,	1,	'tableOfContents',	'Table Of Contents',	'A list of subunits of the resource.'),
(19,	NULL,	1,	'abstract',	'Abstract',	'A summary of the resource.'),
(20,	NULL,	1,	'created',	'Date Created',	'Date of creation of the resource.'),
(21,	NULL,	1,	'valid',	'Date Valid',	'Date (often a range) of validity of a resource.'),
(22,	NULL,	1,	'available',	'Date Available',	'Date (often a range) that the resource became or will become available.'),
(23,	NULL,	1,	'issued',	'Date Issued',	'Date of formal issuance (e.g., publication) of the resource.'),
(24,	NULL,	1,	'modified',	'Date Modified',	'Date on which the resource was changed.'),
(25,	NULL,	1,	'extent',	'Extent',	'The size or duration of the resource.'),
(26,	NULL,	1,	'medium',	'Medium',	'The material or physical carrier of the resource.'),
(27,	NULL,	1,	'isVersionOf',	'Is Version Of',	'A related resource of which the described resource is a version, edition, or adaptation.'),
(28,	NULL,	1,	'hasVersion',	'Has Version',	'A related resource that is a version, edition, or adaptation of the described resource.'),
(29,	NULL,	1,	'isReplacedBy',	'Is Replaced By',	'A related resource that supplants, displaces, or supersedes the described resource.'),
(30,	NULL,	1,	'replaces',	'Replaces',	'A related resource that is supplanted, displaced, or superseded by the described resource.'),
(31,	NULL,	1,	'isRequiredBy',	'Is Required By',	'A related resource that requires the described resource to support its function, delivery, or coherence.'),
(32,	NULL,	1,	'requires',	'Requires',	'A related resource that is required by the described resource to support its function, delivery, or coherence.'),
(33,	NULL,	1,	'isPartOf',	'Is Part Of',	'A related resource in which the described resource is physically or logically included.'),
(34,	NULL,	1,	'hasPart',	'Has Part',	'A related resource that is included either physically or logically in the described resource.'),
(35,	NULL,	1,	'isReferencedBy',	'Is Referenced By',	'A related resource that references, cites, or otherwise points to the described resource.'),
(36,	NULL,	1,	'references',	'References',	'A related resource that is referenced, cited, or otherwise pointed to by the described resource.'),
(37,	NULL,	1,	'isFormatOf',	'Is Format Of',	'A related resource that is substantially the same as the described resource, but in another format.'),
(38,	NULL,	1,	'hasFormat',	'Has Format',	'A related resource that is substantially the same as the pre-existing described resource, but in another format.'),
(39,	NULL,	1,	'conformsTo',	'Conforms To',	'An established standard to which the described resource conforms.'),
(40,	NULL,	1,	'spatial',	'Spatial Coverage',	'Spatial characteristics of the resource.'),
(41,	NULL,	1,	'temporal',	'Temporal Coverage',	'Temporal characteristics of the resource.'),
(42,	NULL,	1,	'mediator',	'Mediator',	'An entity that mediates access to the resource and for whom the resource is intended or useful.'),
(43,	NULL,	1,	'dateAccepted',	'Date Accepted',	'Date of acceptance of the resource.'),
(44,	NULL,	1,	'dateCopyrighted',	'Date Copyrighted',	'Date of copyright.'),
(45,	NULL,	1,	'dateSubmitted',	'Date Submitted',	'Date of submission of the resource.'),
(46,	NULL,	1,	'educationLevel',	'Audience Education Level',	'A class of entity, defined in terms of progression through an educational or training context, for which the described resource is intended.'),
(47,	NULL,	1,	'accessRights',	'Access Rights',	'Information about who can access the resource or an indication of its security status.'),
(48,	NULL,	1,	'bibliographicCitation',	'Bibliographic Citation',	'A bibliographic reference for the resource.'),
(49,	NULL,	1,	'license',	'License',	'A legal document giving official permission to do something with the resource.'),
(50,	NULL,	1,	'rightsHolder',	'Rights Holder',	'A person or organization owning or managing rights over the resource.'),
(51,	NULL,	1,	'provenance',	'Provenance',	'A statement of any changes in ownership and custody of the resource since its creation that are significant for its authenticity, integrity, and interpretation.'),
(52,	NULL,	1,	'instructionalMethod',	'Instructional Method',	'A process, used to engender knowledge, attitudes and skills, that the described resource is designed to support.'),
(53,	NULL,	1,	'accrualMethod',	'Accrual Method',	'The method by which items are added to a collection.'),
(54,	NULL,	1,	'accrualPeriodicity',	'Accrual Periodicity',	'The frequency with which items are added to a collection.'),
(55,	NULL,	1,	'accrualPolicy',	'Accrual Policy',	'The policy governing the addition of items to a collection.'),
(56,	NULL,	3,	'affirmedBy',	'affirmedBy',	'A legal decision that affirms a ruling.'),
(57,	NULL,	3,	'annotates',	'annotates',	'Critical or explanatory note for a Document.'),
(58,	NULL,	3,	'authorList',	'list of authors',	'An ordered list of authors. Normally, this list is seen as a priority list that order authors by importance.'),
(59,	NULL,	3,	'citedBy',	'cited by',	'Relates a document to another document that cites the\nfirst document.'),
(60,	NULL,	3,	'cites',	'cites',	'Relates a document to another document that is cited\nby the first document as reference, comment, review, quotation or for\nanother purpose.'),
(61,	NULL,	3,	'contributorList',	'list of contributors',	'An ordered list of contributors. Normally, this list is seen as a priority list that order contributors by importance.'),
(62,	NULL,	3,	'court',	'court',	'A court associated with a legal document; for example, that which issues a decision.'),
(63,	NULL,	3,	'degree',	'degree',	'The thesis degree.'),
(64,	NULL,	3,	'director',	'director',	'A Film director.'),
(65,	NULL,	3,	'distributor',	'distributor',	'Distributor of a document or a collection of documents.'),
(66,	NULL,	3,	'editor',	'editor',	'A person having managerial and sometimes policy-making responsibility for the editorial part of a publishing firm or of a newspaper, magazine, or other publication.'),
(67,	NULL,	3,	'editorList',	'list of editors',	'An ordered list of editors. Normally, this list is seen as a priority list that order editors by importance.'),
(68,	NULL,	3,	'interviewee',	'interviewee',	'An agent that is interviewed by another agent.'),
(69,	NULL,	3,	'interviewer',	'interviewer',	'An agent that interview another agent.'),
(70,	NULL,	3,	'issuer',	'issuer',	'An entity responsible for issuing often informally published documents such as press releases, reports, etc.'),
(71,	NULL,	3,	'organizer',	'organizer',	'The organizer of an event; includes conference organizers, but also government agencies or other bodies that are responsible for conducting hearings.'),
(72,	NULL,	3,	'owner',	'owner',	'Owner of a document or a collection of documents.'),
(73,	NULL,	3,	'performer',	'performer',	NULL),
(74,	NULL,	3,	'presentedAt',	'presented at',	'Relates a document to an event; for example, a paper to a conference.'),
(75,	NULL,	3,	'presents',	'presents',	'Relates an event to associated documents; for example, conference to a paper.'),
(76,	NULL,	3,	'producer',	'producer',	'Producer of a document or a collection of documents.'),
(77,	NULL,	3,	'recipient',	'recipient',	'An agent that receives a communication document.'),
(78,	NULL,	3,	'reproducedIn',	'reproducedIn',	'The resource in which another resource is reproduced.'),
(79,	NULL,	3,	'reversedBy',	'reversedBy',	'A legal decision that reverses a ruling.'),
(80,	NULL,	3,	'reviewOf',	'review of',	'Relates a review document to a reviewed thing (resource, item, etc.).'),
(81,	NULL,	3,	'status',	'status',	'The publication status of (typically academic) content.'),
(82,	NULL,	3,	'subsequentLegalDecision',	'subsequentLegalDecision',	'A legal decision on appeal that takes action on a case (affirming it, reversing it, etc.).'),
(83,	NULL,	3,	'transcriptOf',	'transcript of',	'Relates a document to some transcribed original.'),
(84,	NULL,	3,	'translationOf',	'translation of',	'Relates a translated document to the original document.'),
(85,	NULL,	3,	'translator',	'translator',	'A person who translates written document from one language to another.'),
(86,	NULL,	3,	'abstract',	'abstract',	'A summary of the resource.'),
(87,	NULL,	3,	'argued',	'date argued',	'The date on which a legal case is argued before a court. Date is of format xsd:date'),
(88,	NULL,	3,	'asin',	'asin',	NULL),
(89,	NULL,	3,	'chapter',	'chapter',	'An chapter number'),
(90,	NULL,	3,	'coden',	'coden',	NULL),
(91,	NULL,	3,	'content',	'content',	'This property is for a plain-text rendering of the content of a Document. While the plain-text content of an entire document could be described by this property.'),
(92,	NULL,	3,	'doi',	'doi',	NULL),
(93,	NULL,	3,	'eanucc13',	'eanucc13',	NULL),
(94,	NULL,	3,	'edition',	'edition',	'The name defining a special edition of a document. Normally its a literal value composed of a version number and words.'),
(95,	NULL,	3,	'eissn',	'eissn',	NULL),
(96,	NULL,	3,	'gtin14',	'gtin14',	NULL),
(97,	NULL,	3,	'handle',	'handle',	NULL),
(98,	NULL,	3,	'identifier',	'identifier',	NULL),
(99,	NULL,	3,	'isbn',	'isbn',	NULL),
(100,	NULL,	3,	'isbn10',	'isbn10',	NULL),
(101,	NULL,	3,	'isbn13',	'isbn13',	NULL),
(102,	NULL,	3,	'issn',	'issn',	NULL),
(103,	NULL,	3,	'issue',	'issue',	'An issue number'),
(104,	NULL,	3,	'lccn',	'lccn',	NULL),
(105,	NULL,	3,	'locator',	'locator',	'A description (often numeric) that locates an item within a containing document or collection.'),
(106,	NULL,	3,	'numPages',	'number of pages',	'The number of pages contained in a document'),
(107,	NULL,	3,	'numVolumes',	'number of volumes',	'The number of volumes contained in a collection of documents (usually a series, periodical, etc.).'),
(108,	NULL,	3,	'number',	'number',	'A generic item or document number. Not to be confused with issue number.'),
(109,	NULL,	3,	'oclcnum',	'oclcnum',	NULL),
(110,	NULL,	3,	'pageEnd',	'page end',	'Ending page number within a continuous page range.'),
(111,	NULL,	3,	'pageStart',	'page start',	'Starting page number within a continuous page range.'),
(112,	NULL,	3,	'pages',	'pages',	'A string of non-contiguous page spans that locate a Document within a Collection. Example: 23-25, 34, 54-56. For continuous page ranges, use the pageStart and pageEnd properties.'),
(113,	NULL,	3,	'pmid',	'pmid',	NULL),
(114,	NULL,	3,	'prefixName',	'prefix name',	'The prefix of a name'),
(115,	NULL,	3,	'section',	'section',	'A section number'),
(116,	NULL,	3,	'shortDescription',	'shortDescription',	NULL),
(117,	NULL,	3,	'shortTitle',	'short title',	'The abbreviation of a title.'),
(118,	NULL,	3,	'sici',	'sici',	NULL),
(119,	NULL,	3,	'suffixName',	'suffix name',	'The suffix of a name'),
(120,	NULL,	3,	'upc',	'upc',	NULL),
(121,	NULL,	3,	'uri',	'uri',	'Universal Resource Identifier of a document'),
(122,	NULL,	3,	'volume',	'volume',	'A volume number'),
(123,	NULL,	4,	'mbox',	'personal mailbox',	'A  personal mailbox, ie. an Internet mailbox associated with exactly one owner, the first owner of this mailbox. This is a \'static inverse functional property\', in that  there is (across time and change) at most one individual that ever has any particular value for foaf:mbox.'),
(124,	NULL,	4,	'mbox_sha1sum',	'sha1sum of a personal mailbox URI name',	'The sha1sum of the URI of an Internet mailbox associated with exactly one owner, the  first owner of the mailbox.'),
(125,	NULL,	4,	'gender',	'gender',	'The gender of this Agent (typically but not necessarily \'male\' or \'female\').'),
(126,	NULL,	4,	'geekcode',	'geekcode',	'A textual geekcode for this person, see http://www.geekcode.com/geek.html'),
(127,	NULL,	4,	'dnaChecksum',	'DNA checksum',	'A checksum for the DNA of some thing. Joke.'),
(128,	NULL,	4,	'sha1',	'sha1sum (hex)',	'A sha1sum hash, in hex.'),
(129,	NULL,	4,	'based_near',	'based near',	'A location that something is based near, for some broadly human notion of near.'),
(130,	NULL,	4,	'title',	'title',	'Title (Mr, Mrs, Ms, Dr. etc)'),
(131,	NULL,	4,	'nick',	'nickname',	'A short informal nickname characterising an agent (includes login identifiers, IRC and other chat nicknames).'),
(132,	NULL,	4,	'jabberID',	'jabber ID',	'A jabber ID for something.'),
(133,	NULL,	4,	'aimChatID',	'AIM chat ID',	'An AIM chat ID'),
(134,	NULL,	4,	'skypeID',	'Skype ID',	'A Skype ID'),
(135,	NULL,	4,	'icqChatID',	'ICQ chat ID',	'An ICQ chat ID'),
(136,	NULL,	4,	'yahooChatID',	'Yahoo chat ID',	'A Yahoo chat ID'),
(137,	NULL,	4,	'msnChatID',	'MSN chat ID',	'An MSN chat ID'),
(138,	NULL,	4,	'name',	'name',	'A name for some thing.'),
(139,	NULL,	4,	'firstName',	'firstName',	'The first name of a person.'),
(140,	NULL,	4,	'lastName',	'lastName',	'The last name of a person.'),
(141,	NULL,	4,	'givenName',	'Given name',	'The given name of some person.'),
(142,	NULL,	4,	'givenname',	'Given name',	'The given name of some person.'),
(143,	NULL,	4,	'surname',	'Surname',	'The surname of some person.'),
(144,	NULL,	4,	'family_name',	'family_name',	'The family name of some person.'),
(145,	NULL,	4,	'familyName',	'familyName',	'The family name of some person.'),
(146,	NULL,	4,	'phone',	'phone',	'A phone,  specified using fully qualified tel: URI scheme (refs: http://www.w3.org/Addressing/schemes.html#tel).'),
(147,	NULL,	4,	'homepage',	'homepage',	'A homepage for some thing.'),
(148,	NULL,	4,	'weblog',	'weblog',	'A weblog of some thing (whether person, group, company etc.).'),
(149,	NULL,	4,	'openid',	'openid',	'An OpenID for an Agent.'),
(150,	NULL,	4,	'tipjar',	'tipjar',	'A tipjar document for this agent, describing means for payment and reward.'),
(151,	NULL,	4,	'plan',	'plan',	'A .plan comment, in the tradition of finger and \'.plan\' files.'),
(152,	NULL,	4,	'made',	'made',	'Something that was made by this agent.'),
(153,	NULL,	4,	'maker',	'maker',	'An agent that  made this thing.'),
(154,	NULL,	4,	'img',	'image',	'An image that can be used to represent some thing (ie. those depictions which are particularly representative of something, eg. one\'s photo on a homepage).'),
(155,	NULL,	4,	'depiction',	'depiction',	'A depiction of some thing.'),
(156,	NULL,	4,	'depicts',	'depicts',	'A thing depicted in this representation.'),
(157,	NULL,	4,	'thumbnail',	'thumbnail',	'A derived thumbnail image.'),
(158,	NULL,	4,	'myersBriggs',	'myersBriggs',	'A Myers Briggs (MBTI) personality classification.'),
(159,	NULL,	4,	'workplaceHomepage',	'workplace homepage',	'A workplace homepage of some person; the homepage of an organization they work for.'),
(160,	NULL,	4,	'workInfoHomepage',	'work info homepage',	'A work info homepage of some person; a page about their work for some organization.'),
(161,	NULL,	4,	'schoolHomepage',	'schoolHomepage',	'A homepage of a school attended by the person.'),
(162,	NULL,	4,	'knows',	'knows',	'A person known by this person (indicating some level of reciprocated interaction between the parties).'),
(163,	NULL,	4,	'interest',	'interest',	'A page about a topic of interest to this person.'),
(164,	NULL,	4,	'topic_interest',	'topic_interest',	'A thing of interest to this person.'),
(165,	NULL,	4,	'publications',	'publications',	'A link to the publications of this person.'),
(166,	NULL,	4,	'currentProject',	'current project',	'A current project this person works on.'),
(167,	NULL,	4,	'pastProject',	'past project',	'A project this person has previously worked on.'),
(168,	NULL,	4,	'fundedBy',	'funded by',	'An organization funding a project or person.'),
(169,	NULL,	4,	'logo',	'logo',	'A logo representing some thing.'),
(170,	NULL,	4,	'topic',	'topic',	'A topic of some page or document.'),
(171,	NULL,	4,	'primaryTopic',	'primary topic',	'The primary topic of some page or document.'),
(172,	NULL,	4,	'focus',	'focus',	'The underlying or \'focal\' entity associated with some SKOS-described concept.'),
(173,	NULL,	4,	'isPrimaryTopicOf',	'is primary topic of',	'A document that this thing is the primary topic of.'),
(174,	NULL,	4,	'page',	'page',	'A page or document about this thing.'),
(175,	NULL,	4,	'theme',	'theme',	'A theme.'),
(176,	NULL,	4,	'account',	'account',	'Indicates an account held by this agent.'),
(177,	NULL,	4,	'holdsAccount',	'account',	'Indicates an account held by this agent.'),
(178,	NULL,	4,	'accountServiceHomepage',	'account service homepage',	'Indicates a homepage of the service provide for this online account.'),
(179,	NULL,	4,	'accountName',	'account name',	'Indicates the name (identifier) associated with this online account.'),
(180,	NULL,	4,	'member',	'member',	'Indicates a member of a Group'),
(181,	NULL,	4,	'membershipClass',	'membershipClass',	'Indicates the class of individuals that are a member of a Group'),
(182,	NULL,	4,	'birthday',	'birthday',	'The birthday of this Agent, represented in mm-dd string form, eg. \'12-31\'.'),
(183,	NULL,	4,	'age',	'age',	'The age in years of some agent.'),
(184,	NULL,	4,	'status',	'status',	'A string expressing what the user is happy for the general public (normally) to know about their current activity.');

TRUNCATE `resource_class`;
INSERT INTO `resource_class` (`id`, `owner_id`, `vocabulary_id`, `local_name`, `label`, `comment`) VALUES
(1,	NULL,	1,	'Agent',	'Agent',	'A resource that acts or has the power to act.'),
(2,	NULL,	1,	'AgentClass',	'Agent Class',	'A group of agents.'),
(3,	NULL,	1,	'BibliographicResource',	'Bibliographic Resource',	'A book, article, or other documentary resource.'),
(4,	NULL,	1,	'FileFormat',	'File Format',	'A digital resource format.'),
(5,	NULL,	1,	'Frequency',	'Frequency',	'A rate at which something recurs.'),
(6,	NULL,	1,	'Jurisdiction',	'Jurisdiction',	'The extent or range of judicial, law enforcement, or other authority.'),
(7,	NULL,	1,	'LicenseDocument',	'License Document',	'A legal document giving official permission to do something with a Resource.'),
(8,	NULL,	1,	'LinguisticSystem',	'Linguistic System',	'A system of signs, symbols, sounds, gestures, or rules used in communication.'),
(9,	NULL,	1,	'Location',	'Location',	'A spatial region or named place.'),
(10,	NULL,	1,	'LocationPeriodOrJurisdiction',	'Location, Period, or Jurisdiction',	'A location, period of time, or jurisdiction.'),
(11,	NULL,	1,	'MediaType',	'Media Type',	'A file format or physical medium.'),
(12,	NULL,	1,	'MediaTypeOrExtent',	'Media Type or Extent',	'A media type or extent.'),
(13,	NULL,	1,	'MethodOfInstruction',	'Method of Instruction',	'A process that is used to engender knowledge, attitudes, and skills.'),
(14,	NULL,	1,	'MethodOfAccrual',	'Method of Accrual',	'A method by which resources are added to a collection.'),
(15,	NULL,	1,	'PeriodOfTime',	'Period of Time',	'An interval of time that is named or defined by its start and end dates.'),
(16,	NULL,	1,	'PhysicalMedium',	'Physical Medium',	'A physical material or carrier.'),
(17,	NULL,	1,	'PhysicalResource',	'Physical Resource',	'A material thing.'),
(18,	NULL,	1,	'Policy',	'Policy',	'A plan or course of action by an authority, intended to influence and determine decisions, actions, and other matters.'),
(19,	NULL,	1,	'ProvenanceStatement',	'Provenance Statement',	'A statement of any changes in ownership and custody of a resource since its creation that are significant for its authenticity, integrity, and interpretation.'),
(20,	NULL,	1,	'RightsStatement',	'Rights Statement',	'A statement about the intellectual property rights (IPR) held in or over a Resource, a legal document giving official permission to do something with a resource, or a statement about access rights.'),
(21,	NULL,	1,	'SizeOrDuration',	'Size or Duration',	'A dimension or extent, or a time taken to play or execute.'),
(22,	NULL,	1,	'Standard',	'Standard',	'A basis for comparison; a reference point against which other things can be evaluated.'),
(23,	NULL,	2,	'Collection',	'Collection',	'An aggregation of resources.'),
(24,	NULL,	2,	'Dataset',	'Dataset',	'Data encoded in a defined structure.'),
(25,	NULL,	2,	'Event',	'Event',	'A non-persistent, time-based occurrence.'),
(26,	NULL,	2,	'Image',	'Image',	'A visual representation other than text.'),
(27,	NULL,	2,	'InteractiveResource',	'Interactive Resource',	'A resource requiring interaction from the user to be understood, executed, or experienced.'),
(28,	NULL,	2,	'Service',	'Service',	'A system that provides one or more functions.'),
(29,	NULL,	2,	'Software',	'Software',	'A computer program in source or compiled form.'),
(30,	NULL,	2,	'Sound',	'Sound',	'A resource primarily intended to be heard.'),
(31,	NULL,	2,	'Text',	'Text',	'A resource consisting primarily of words for reading.'),
(32,	NULL,	2,	'PhysicalObject',	'Physical Object',	'An inanimate, three-dimensional object or substance.'),
(33,	NULL,	2,	'StillImage',	'Still Image',	'A static visual representation.'),
(34,	NULL,	2,	'MovingImage',	'Moving Image',	'A series of visual representations imparting an impression of motion when shown in succession.'),
(35,	NULL,	3,	'AcademicArticle',	'Academic Article',	'A scholarly academic article, typically published in a journal.'),
(36,	NULL,	3,	'Article',	'Article',	'A written composition in prose, usually nonfiction, on a specific topic, forming an independent part of a book or other publication, as a newspaper or magazine.'),
(37,	NULL,	3,	'AudioDocument',	'audio document',	'An audio document; aka record.'),
(38,	NULL,	3,	'AudioVisualDocument',	'audio-visual document',	'An audio-visual document; film, video, and so forth.'),
(39,	NULL,	3,	'Bill',	'Bill',	'Draft legislation presented for discussion to a legal body.'),
(40,	NULL,	3,	'Book',	'Book',	'A written or printed work of fiction or nonfiction, usually on sheets of paper fastened or bound together within covers.'),
(41,	NULL,	3,	'BookSection',	'Book Section',	'A section of a book.'),
(42,	NULL,	3,	'Brief',	'Brief',	'A written argument submitted to a court.'),
(43,	NULL,	3,	'Chapter',	'Chapter',	'A chapter of a book.'),
(44,	NULL,	3,	'Code',	'Code',	'A collection of statutes.'),
(45,	NULL,	3,	'CollectedDocument',	'Collected Document',	'A document that simultaneously contains other documents.'),
(46,	NULL,	3,	'Collection',	'Collection',	'A collection of Documents or Collections'),
(47,	NULL,	3,	'Conference',	'Conference',	'A meeting for consultation or discussion.'),
(48,	NULL,	3,	'CourtReporter',	'Court Reporter',	'A collection of legal cases.'),
(49,	NULL,	3,	'Document',	'Document',	'A document (noun) is a bounded physical representation of body of information designed with the capacity (and usually intent) to communicate. A document may manifest symbolic, diagrammatic or sensory-representational information.'),
(50,	NULL,	3,	'DocumentPart',	'document part',	'a distinct part of a larger document or collected document.'),
(51,	NULL,	3,	'DocumentStatus',	'Document Status',	'The status of the publication of a document.'),
(52,	NULL,	3,	'EditedBook',	'Edited Book',	'An edited book.'),
(53,	NULL,	3,	'Email',	'EMail',	'A written communication addressed to a person or organization and transmitted electronically.'),
(54,	NULL,	3,	'Event',	'Event',	NULL),
(55,	NULL,	3,	'Excerpt',	'Excerpt',	'A passage selected from a larger work.'),
(56,	NULL,	3,	'Film',	'Film',	'aka movie.'),
(57,	NULL,	3,	'Hearing',	'Hearing',	'An instance or a session in which testimony and arguments are presented, esp. before an official, as a judge in a lawsuit.'),
(58,	NULL,	3,	'Image',	'Image',	'A document that presents visual or diagrammatic information.'),
(59,	NULL,	3,	'Interview',	'Interview',	'A formalized discussion between two or more people.'),
(60,	NULL,	3,	'Issue',	'Issue',	'something that is printed or published and distributed, esp. a given number of a periodical'),
(61,	NULL,	3,	'Journal',	'Journal',	'A periodical of scholarly journal Articles.'),
(62,	NULL,	3,	'LegalCaseDocument',	'Legal Case Document',	'A document accompanying a legal case.'),
(63,	NULL,	3,	'LegalDecision',	'Decision',	'A document containing an authoritative determination (as a decree or judgment) made after consideration of facts or law.'),
(64,	NULL,	3,	'LegalDocument',	'Legal Document',	'A legal document; for example, a court decision, a brief, and so forth.'),
(65,	NULL,	3,	'Legislation',	'Legislation',	'A legal document proposing or enacting a law or a group of laws.'),
(66,	NULL,	3,	'Letter',	'Letter',	'A written or printed communication addressed to a person or organization and usually transmitted by mail.'),
(67,	NULL,	3,	'Magazine',	'Magazine',	'A periodical of magazine Articles. A magazine is a publication that is issued periodically, usually bound in a paper cover, and typically contains essays, stories, poems, etc., by many writers, and often photographs and drawings, frequently specializing in a particular subject or area, as hobbies, news, or sports.'),
(68,	NULL,	3,	'Manual',	'Manual',	'A small reference book, especially one giving instructions.'),
(69,	NULL,	3,	'Manuscript',	'Manuscript',	'An unpublished Document, which may also be submitted to a publisher for publication.'),
(70,	NULL,	3,	'Map',	'Map',	'A graphical depiction of geographic features.'),
(71,	NULL,	3,	'MultiVolumeBook',	'Multivolume Book',	'A loose, thematic, collection of Documents, often Books.'),
(72,	NULL,	3,	'Newspaper',	'Newspaper',	'A periodical of documents, usually issued daily or weekly, containing current news, editorials, feature articles, and usually advertising.'),
(73,	NULL,	3,	'Note',	'Note',	'Notes or annotations about a resource.'),
(74,	NULL,	3,	'Patent',	'Patent',	'A document describing the exclusive right granted by a government to an inventor to manufacture, use, or sell an invention for a certain number of years.'),
(75,	NULL,	3,	'Performance',	'Performance',	'A public performance.'),
(76,	NULL,	3,	'Periodical',	'Periodical',	'A group of related documents issued at regular intervals.'),
(77,	NULL,	3,	'PersonalCommunication',	'Personal Communication',	'A communication between an agent and one or more specific recipients.'),
(78,	NULL,	3,	'PersonalCommunicationDocument',	'Personal Communication Document',	'A personal communication manifested in some document.'),
(79,	NULL,	3,	'Proceedings',	'Proceedings',	'A compilation of documents published from an event, such as a conference.'),
(80,	NULL,	3,	'Quote',	'Quote',	'An excerpted collection of words.'),
(81,	NULL,	3,	'ReferenceSource',	'Reference Source',	'A document that presents authoritative reference information, such as a dictionary or encylopedia .'),
(82,	NULL,	3,	'Report',	'Report',	'A document describing an account or statement describing in detail an event, situation, or the like, usually as the result of observation, inquiry, etc..'),
(83,	NULL,	3,	'Series',	'Series',	'A loose, thematic, collection of Documents, often Books.'),
(84,	NULL,	3,	'Slide',	'Slide',	'A slide in a slideshow'),
(85,	NULL,	3,	'Slideshow',	'Slideshow',	'A presentation of a series of slides, usually presented in front of an audience with written text and images.'),
(86,	NULL,	3,	'Standard',	'Standard',	'A document describing a standard'),
(87,	NULL,	3,	'Statute',	'Statute',	'A bill enacted into law.'),
(88,	NULL,	3,	'Thesis',	'Thesis',	'A document created to summarize research findings associated with the completion of an academic degree.'),
(89,	NULL,	3,	'ThesisDegree',	'Thesis degree',	'The academic degree of a Thesis'),
(90,	NULL,	3,	'Webpage',	'Webpage',	'A web page is an online document available (at least initially) on the world wide web. A web page is written first and foremost to appear on the web, as distinct from other online resources such as books, manuscripts or audio documents which use the web primarily as a distribution mechanism alongside other more traditional methods such as print.'),
(91,	NULL,	3,	'Website',	'Website',	'A group of Webpages accessible on the Web.'),
(92,	NULL,	3,	'Workshop',	'Workshop',	'A seminar, discussion group, or the like, that emphasizes zxchange of ideas and the demonstration and application of techniques, skills, etc.'),
(93,	NULL,	4,	'LabelProperty',	'Label Property',	'A foaf:LabelProperty is any RDF property with texual values that serve as labels.'),
(94,	NULL,	4,	'Person',	'Person',	'A person.'),
(95,	NULL,	4,	'Agent',	'Agent',	'An agent (eg. person, group, software or physical artifact).'),
(96,	NULL,	4,	'Document',	'Document',	'A document.'),
(97,	NULL,	4,	'Organization',	'Organization',	'An organization.'),
(98,	NULL,	4,	'Group',	'Group',	'A class of Agents.'),
(99,	NULL,	4,	'Project',	'Project',	'A project (a collective endeavour of some kind).'),
(100,	NULL,	4,	'Image',	'Image',	'An image.'),
(101,	NULL,	4,	'PersonalProfileDocument',	'PersonalProfileDocument',	'A personal profile RDF document.'),
(102,	NULL,	4,	'OnlineAccount',	'Online Account',	'An online account.'),
(103,	NULL,	4,	'OnlineGamingAccount',	'Online Gaming Account',	'An online gaming account.'),
(104,	NULL,	4,	'OnlineEcommerceAccount',	'Online E-commerce Account',	'An online e-commerce account.'),
(105,	NULL,	4,	'OnlineChatAccount',	'Online Chat Account',	'An online chat account.');

TRUNCATE `vocabulary`;
INSERT INTO `vocabulary` (`id`, `owner_id`, `namespace_uri`, `prefix`, `label`, `comment`) VALUES
(1,	NULL,	'http://purl.org/dc/terms/',	'dcterms',	'Dublin Core',	'Basic resource metadata (DCMI Metadata Terms)'),
(2,	NULL,	'http://purl.org/dc/dcmitype/',	'dctype',	'Dublin Core Type',	'Basic resource types (DCMI Type Vocabulary)'),
(3,	NULL,	'http://purl.org/ontology/bibo/',	'bibo',	'Bibliographic Ontology',	'Bibliographic metadata (BIBO)'),
(4,	NULL,	'http://xmlns.com/foaf/0.1/',	'foaf',	'Friend of a Friend',	'Relationships between people and organizations (FOAF)');

SET foreign_key_checks = 1;
