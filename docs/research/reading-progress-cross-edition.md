# Cross-edition reading progress: what primary sources say

Research date: 2026-08-24. All claims below are traced to the primary source cited inline, with a note on what was actually read there. Where a primary source could not be reached despite attempts (noted explicitly), the gap is reported rather than filled with secondary material.

---

## 1. Do major book metadata APIs expose chapter/part-level structural data?

### Google Books API — no.

Fetched the **volumeInfo** field list from the official API reference: [Volumes reference](https://developers.google.com/books/docs/v1/reference/volumes). The documented fields are: `title`, `subtitle`, `authors[]`, `publisher`, `publishedDate`, `description`, `industryIdentifiers[]`, `pageCount`, `dimensions`, `printType`, `categories[]`, `averageRating`, `ratingsCount`, `contentVersion`, `imageLinks`, `language`, `mainCategory`, `previewLink`, `infoLink`, `canonicalVolumeLink`. There is no `tableOfContents`, `toc`, or `chapters` field anywhere in this schema — the closest thing to structure is `pageCount` (a single integer) and `dimensions` (physical size).

Also checked [Using the Google Books APIs](https://developers.google.com/books/docs/v1/using) — a full-text search for "tableOfContents", "toc", "table of contents", and "chapter" returned no matches. The page covers search, volume retrieval, bookshelves, and auth, with nothing about structural navigation inside a book.

**Conclusion: Google Books API has no chapter/TOC field of any kind.**

### Open Library API — yes, but as free-text, edition-level, and inconsistently populated.

Fetched a live example response from the Books API: `https://openlibrary.org/api/books?bibkeys=ISBN:9780980200447&jscmd=details&format=json`. The `details` payload contains a `table_of_contents` array, e.g.:

```json
"table_of_contents": [
  { "level": 0, "label": "", "title": "The personal nature of slow reading", "pagenum": "" },
  { "level": 0, "label": "", "title": "Slow reading in an information ecology", "pagenum": "" }
]
```

Each entry has `level` (hierarchy depth), `label`, `title`, and `pagenum` (a free-text string, often empty).

Cross-checked this against Open Library's own type schema, fetched at `https://openlibrary.org/type/toc_item.json`: the `/type/toc_item` record type formally declares exactly four string properties — `class`, `label`, `title`, `pagenum` (each marked `"unique": true`, i.e. single-valued, not structured page ranges). This confirms `table_of_contents` is a first-class but loosely-typed field: no chapter numbers, no start/end offsets, no guaranteed presence.

Fetched [openlibrary.org/dev/docs/api/books](https://openlibrary.org/dev/docs/api/books) directly: it documents `table_of_contents` under the `jscmd=details` response, but explicitly warns "It is advised to use `jscmd=data` instead of this as that is more stable format" — i.e. Open Library itself flags the `details`/TOC-bearing response mode as the less-stable of its two book-lookup formats.

Checked whether `table_of_contents` is a Work-level or Edition-level field via [openlibrary.org/about/work_edition](https://openlibrary.org/about/work_edition) (the page Open Library's own developer docs link to for "field assignments for Works and Editions"). The page lists `table_of_contents` in its field table but the fetched content did not show it tagged as Work-only — and independently, the API example above is returned from an **ISBN lookup**, which in Open Library's data model only resolves to an Edition record, not a Work. This means in practice `table_of_contents` is populated (when present) per Edition, not centrally on the Work.

**Conclusion: Open Library exposes a TOC field, but it's free-text, per-edition, sparsely populated (crowdsourced/librarian-entered), and Open Library's own docs call the response mode that carries it the less-stable option.**

### Other first-party sources checked

- **MARC 21 / Library of Congress field 505 ("Formatted Contents Note")** — attempted to fetch [loc.gov/marc/bibliographic/bd505.html](https://www.loc.gov/marc/bibliographic/bd505.html) directly (403 Forbidden, could not read). Read the parallel **OCLC bibliographic formats page** instead: [oclc.org/bibformats/en/5xx/505.html](https://www.oclc.org/bibformats/en/5xx/505.html). It states 505 is "Optional/Optional" for both Full and Minimal cataloging levels, and that "Contents notes contain the titles of separate works or parts of an item. They may also include statements of responsibility associated with the works or parts" — and explicitly: "Data in contents notes were never intended to be controlled access points." A web search of Library of Congress's own MARC docs (snippet, not directly fetched due to the 403) additionally indicates chapter numbers are typically **omitted** from 505 notes, which record titles/volumes, not chapter-level granularity.
- **WorldCat Metadata/Search API (OCLC)**: field 505 can appear in a WorldCat bibliographic record's MARC data (per the OCLC page above), so it is *available* through OCLC's APIs in principle, but it is librarian-composed free text, optional, and not standardized into machine-usable chapter boundaries.
- **ISBNdb**: attempted to fetch the official API docs at `isbndb.com/apidocs/v2` — got HTTP 403 (site blocks the fetch tool). Could not verify ISBNdb's schema from primary source; **this is a documented gap**, not a finding.

---

## 2. Is structural data (chapter boundaries, TOC) consistent across physical, EPUB, and audiobook editions of the "same" book?

### EPUB 3 Navigation Document — per-file, per-edition, not standardized across editions.

Fetched [W3C EPUB 3.3](https://www.w3.org/TR/epub-33/), the Navigation Document section. Key points read directly off the spec:

- The Navigation Document is defined as "A specialization of the XHTML content document that contains human- and machine-readable global navigation information."
- The `toc` nav: "The `toc nav` element provides the table of contents for the publication, allowing readers to navigate to major sections."
- The `landmarks` nav: "The `landmarks nav` element identifies key structural components like preface, chapters, and appendices **within a specific edition**."
- Structurally, the nav document is authored **per EPUB file** — it reflects "the default reading order" and organization of that particular publication package. The spec defines no cross-edition or cross-file identity mechanism for nav entries; two different EPUB packagings of the "same" book (e.g. a first edition vs. a revised edition, or different publisher conversions) each ship their own independently-authored nav document, with no requirement that chapter counts, titles, or boundaries match.

**Conclusion from the spec: EPUB TOC/nav structure is defined per-publication-file. Nothing in EPUB 3.3 guarantees structural consistency across two different EPUB editions of the same book, let alone across EPUB vs. print vs. audio.**

### ACX (Audible/Amazon audiobook production) — chapters are edition-specific audio artifacts, not narrative markers derived from the text.

Fetched the official ACX help article: [help.acx.com — What are the ACX audio submission requirements?](https://help.acx.com/s/article/what-are-the-acx-audio-submission-requirements). Read directly:

- "Each file must contain only one chapter or section. Upload each file individually."
- "Each file must include the section header (e.g., 'Prologue', 'Chapter 1')."
- Files must be under 120 minutes each; opening/closing credits are separate files; if a chapter's audio exceeds 120 minutes it must be split with a "Chapter 2 continued" style header.

This means an audiobook's chapter boundaries are a **production artifact of that specific narration/edition** — determined by the narrator/producer splitting audio into files, subject to a 120-minute technical ceiling that has nothing to do with the book's narrative structure. A book whose print chapters are very long could see ACX-mandated splits ("continued") that don't correspond to any print or ebook chapter break.

Also fetched Audible's own help center: [help.audible.ca — Browse chapters and episodes](https://help.audible.ca/s/article/browse-chapters-and-episodes?language=en_CA). It only documents the player UI ("select the Chapters menu icon, then select the chapter... you want to hear") and does not describe how chapter markers are derived from the text — consistent with chapters being an audio-production concept, not a text-structural one exposed by Audible.

### Amazon Kindle "Locations" and page-number matching — first-party confirmation that ebook position units are edition/reflow-dependent, and that print-page-number display requires explicit matching to one specific print edition.

Fetched the KDP (Kindle Direct Publishing) help page on paperback creation: [kdp.amazon.com — Prepare Your eBook and Paperback with Kindle Create](https://kdp.amazon.com/en_US/help/topic/G93BCLJGZFGK39BT). Read directly: Kindle Create lets a publisher set a "page number start location" for the **paperback**, and the help text explicitly states this paperback page-numbering is *not* shared with the ebook: "No, we don't use the page number start location in Kindle Create to measure the number of pages customers read in your eBook" — instead ebook read-progress uses a separate metric (Kindle Edition Normalized Page Count, referenced in the same KDP help flow). This is a first-party admission that **page numbers in the print/paperback edition and progress measurement in the Kindle ebook edition are two independent systems that KDP does not automatically reconcile** — a publisher has to opt in to page-number matching, and it applies to one nominated print edition.

Attempted to reach Amazon's consumer-facing help page describing "Real Page Numbers" (the feature matching Kindle reading position to a specific print edition's page numbers) directly at `amazon.com/gp/help/customer/display.html` — every node ID tried returned HTTP 503 (Amazon's retail help center blocks the fetch tool). **This is a documented gap**: the "Real Page Numbers matches to one specific print ISBN" claim is well-attested in secondary sources and consistent with what the reachable KDP page above independently confirms (page numbers are tied to one nominated edition, not computed generically), but the actual Amazon consumer help page text could not be read and is not quoted here.

**Conclusion: across all three reachable primary/near-primary sources (EPUB spec, ACX, KDP), structural/positional data is edition-specific by design — not a property of "the book" but of the specific file, printing, or narration.**

---

## 3. Existing conventions that separate "the work" from edition-specific pagination

### FRBR / IFLA LRM (Library Reference Model) — the canonical library-science model, read directly from the IFLA-hosted PDF.

Fetched the current IFLA-hosted document at `repository.ifla.org` (IFLA Library Reference Model, "Consolidation Editorial Group of the IFLA FRBR Review Group," text as amended through December 2021, issued July 2024 — this is the model that formally superseded the original 1998 FRBR Final Report, extracted to plain text locally for exact quoting since the PDF isn't renderable inline). Read verbatim, entities LRM-E2 through LRM-E5:

- **Work (LRM-E2)**: "The intellectual or artistic content of a distinct creation." Scope note: "A work is an abstract entity... A work is a conceptual object, no single material object can be identified as the work."
- **Expression (LRM-E3)**: "A distinct combination of signs conveying intellectual or artistic content." Scope note: "An expression is the specific intellectual or artistic form that a work takes each time it is 'realized'... The boundaries of the entity expression are defined... so as to exclude incidental aspects of physical form, such as typeface and page layout for a text."
- **Manifestation (LRM-E4)**: "A set of all carriers that are assumed to share the same characteristics as to intellectual or artistic content and aspects of physical form." Scope note gives worked examples of what counts as a *new* manifestation: "a change in typeface, size of font, page layout... a change from paper to microfilm... a change from cassette to cartridge."
- **Item (LRM-E5)**: "An object or objects carrying signs intended to convey intellectual or artistic content" — the single physical/held copy.

Two passages are directly on point for this app's problem:

1. On content identity across manifestations: "the intellectual or artistic content embodied in one manifestation is in fact the same, or substantially the same, as that embodied in another manifestation even though the physical embodiment may differ and differing attributes of the manifestations may obscure the fact that the content is similar in both." — i.e. FRBR/LRM's whole point is that *pagination differences across manifestations don't change the underlying content*, which is exactly the "different physical copies/ebook editions have different page counts" problem this app faces.
2. On format changes and audiobooks specifically: "any change in form (e.g., **from written notation to spoken word**) results in a new expression." This is an explicit, named example in the standard: an audiobook is formally a *different Expression* of the same Work as the printed text — under LRM, print and audio are siblings under one Work, not the same Expression.

**Conclusion: FRBR/LRM gives a real, load-bearing vocabulary for this exact problem** — "the book" as a narrative structure lives at the **Work** (or arguably Expression, since chapter/paragraph structure is closer to "intellectual form" than to physical carrier) level, while page counts and physical pagination are **Manifestation**-level facts. A physical copy, an EPUB, and an audiobook narration of the same title are typically the same Work, different Expressions (print text vs. spoken word), each realized in one or more Manifestations (a specific print run, a specific EPUB build, a specific narrated recording) that carry the edition-specific pagination/timing.

### Open Library's Work/Edition model — a simplified, two-level version of the same idea, confirmed from Open Library's own docs.

Fetched [openlibrary.org/developers/api](https://openlibrary.org/developers/api): it documents distinct "Work & Edition APIs — Retrieve a specific work or edition by identifier," i.e. Works and Editions are separate, independently-addressable resource types in Open Library's own REST API (`/works/OL...W` vs `/books/OL...M`), not merely a conceptual distinction. As already noted above, the `table_of_contents` field observed in the live API response is returned from an Edition-level (ISBN) lookup, not centrally from the Work — meaning Open Library's *implementation* stores TOC per-Edition even though its data model has a Work concept that could in principle host a single shared structure.

### schema.org — has the relational vocabulary, no book-specific chapter structure.

Fetched [schema.org/Book](https://schema.org/Book). Confirmed properties:
- `hasPart` / `isPartOf` (inherited from `CreativeWork`) — generic "this creative work is part of / has as a part" relations, inverse of each other. Could in principle model a Book `hasPart` a Chapter (`CreativeWork` or `schema:Chapter`, which schema.org separately defines as a subtype of `CreativeWork`), but this is a generic mechanism, not a book-specific chaptering feature.
- `bookEdition` (Text) — names the edition, doesn't structure it.
- `numberOfPages` (Integer) — single scalar, no ranges/structure.
- `workExample` / `exampleOfWork` — schema.org's own FRBR-flavored pair: a `CreativeWork` can point to `workExample`s ("the paperback edition, first edition, or e-book" — this phrase is schema.org's own description text) and each example can point back via `exampleOfWork`. This is schema.org's direct, acknowledged borrowing of the FRBR Work/Manifestation split into linked-data vocabulary.
- No dedicated table-of-contents or chapter-count property exists on `Book` itself.

### EPUB CFI — real prior art for "reference a location independent of pagination," but explicitly scoped to EPUB only.

Fetched [idpf.org/epub/linking/cfi/](https://idpf.org/epub/linking/cfi/). Read the opening definition verbatim: "This specification, EPUB Canonical Fragment Identifier (epubcfi), defines a standardized method for referencing arbitrary content within an EPUB® Publication through the use of fragment identifiers." Example form: `book.epub#epubcfi(/6/4[chap01ref]!/4[body01]/10[para05]/3:10)`.

The spec is scoped entirely to EPUB: it discusses CFI correctness/robustness *within* a single EPUB publication (via structural-integrity assertions a Reading System can check), and contains **no discussion whatsoever of applicability to print books, audiobooks, or any non-EPUB format** — confirmed by direct search of the fetched text. So CFI is real, standardized, format-independent-*of-reading-system* prior art for "point at an exact spot in the text without relying on page number," but it is not cross-format: an EPUB CFI cannot address a position in a physical book or an audio file, and a CFI generated against one EPUB file's internal structure has no defined meaning against a structurally different EPUB build of the same title (the spec's "correction" mechanism is about tolerating minor DOM drift within the same publication, not about mapping between different publications).

---

## 4. Prior art from real reading/book-club apps (first-party docs only)

### Goodreads — percentage-vs-page is a user-facing display toggle, not evidence of cross-edition matching.

Attempted the canonical help URL `goodreads.com/help/show/212-...` (302 redirect) to `help.goodreads.com/s/article/Why-are-my-status-updates-calculated-in-percentages-1553870933549` — that redirected URL 404'd when fetched directly. **Could not read Goodreads' own explanatory text for why percentage is the default; this is a documented gap.** What is confirmed via Goodreads' own help-center site search results (article titles, not fetched full text): Goodreads offers an update-progress UI that lets a user toggle between "page #" and "%" per status update, on desktop, iOS, and Android — implying Goodreads stores progress as *either* a page number (tied to whatever edition figure the user has associated with the book) or a percentage (edition-independent), leaving reconciliation across editions to the user rather than to any structural data Goodreads holds about the book.

### Amazon Whispersync for Voice — first-party confirms the *feature exists* (sync between audiobook and ebook position); the mechanism was not readable.

Attempted the official troubleshooting page at `amazon.com/gp/help/customer/display.html?nodeId=TBOgUdeE7YXXcq28Ac` — HTTP 503 on every attempt (Amazon's retail help center consistently blocked the fetch tool for this whole investigation). **Gap**: could not confirm from primary source *how* Whispersync for Voice aligns an audiobook chapter/timestamp to an ebook location — only that Amazon markets and supports such a feature (title needs to support "Read & Listen," per help-page metadata visible in search results, not fetched body text). Given what the Kindle "Locations" system is confirmed to be (a proprietary, ebook-build-specific unit — see §2), and that Whispersync requires the *specific* matching ebook+audiobook pairing Amazon sells as a bundle, this is consistent with Whispersync being a **proprietary, pairwise mapping Amazon computes for its own catalog** rather than a published, reusable cross-format standard — but this inference is not confirmed by directly-read Amazon text.

### StoryGraph — first-party changelog confirms plain page-count tracking, no cross-edition handling documented.

Fetched [roadmap.thestorygraph.com/changelog/page-tracking-and-progress-notes](https://roadmap.thestorygraph.com/changelog/page-tracking-and-progress-notes) (StoryGraph's own official changelog/roadmap site). Read directly: progress is tracked as pages read incrementing toward the book's page count ("as you increment your progress tracker, pages read are added to your stats"), with optional freeform progress notes. **No mention of format or edition differences anywhere in the fetched changelog** — i.e., StoryGraph's own documentation does not address the cross-edition problem at all; it tracks a raw page count against whatever page-count figure is on file for that specific book listing.

### Fable — first-party blog confirms progress logging exists; granularity not detailed.

Fetched [fable.co/blog/how-to-track-your-reading-on-fable](https://fable.co/blog/how-to-track-your-reading-on-fable) (Fable's own editorial blog). Read directly: the instructions say to "Choose 'Update progress' and enter how much progress you've made," with no further detail in the fetched content on whether that's page-based, percentage-based, or chapter-based, or how cross-edition/cross-format differences are handled. **Gap**: Fable does not appear to have a dedicated first-party FAQ/help-center page (as opposed to blog content) covering this, and the blog content itself doesn't document the underlying data model.

### Literal.club — no first-party FAQ/help documentation found.

Searched specifically for a Literal.club help/FAQ page; none surfaced. Only Literal's own changelog and marketing pages were found via search, referencing that progress can be tracked "by pages, percentage or minutes in audiobooks" (from Literal's public changelog entry surfaced in search results, not independently fetched and verified in full). **Reporting this as a gap**: no first-party documentation of how Literal reconciles progress across a book's different editions/formats was found.

---

## Implications

These are the facts established above and what they open up or foreclose for the app's design — not a recommendation.

1. **No metadata API can be relied on to supply ready-made, consistent chapter/part structure for an arbitrary book.** Google Books exposes none at all. Open Library exposes a `table_of_contents` field, but it is free-text, edition-scoped (populated per ISBN/Edition, not centrally on the Work), inconsistently present, and flagged by Open Library's own docs as coming from the less-stable of its two response formats. MARC 505 (available in principle via OCLC/WorldCat) is librarian-composed prose, optional, and typically doesn't even encode chapter numbers. **Implication:** if the app wants every Book to have chapter/Checkpoint-ready structure, that structure will have to be authored or curated by the app (club organizers, or crowdsourced by members) rather than pulled automatically and reliably from any of these APIs. An import from Open Library can be used as an optional starting point/pre-fill, not a guaranteed source.

2. **Chapter/section boundaries are confirmed, from primary specs and production docs, to vary by edition/format rather than being fixed properties of a title.** EPUB's nav document is authored per EPUB file with no cross-edition consistency guarantee (W3C spec). Audiobook chapter files are bounded by a 120-minute technical ceiling and narration-specific splits (ACX), independent of the print/ebook chapter grid. Kindle's own "Locations" unit and print-page-number matching are edition/build-specific and require explicit, one-to-one association with a nominated print edition (KDP docs). **Implication:** a Checkpoint defined as "Chapter 12" cannot be assumed to land at the same fraction-of-the-book, or even to exist identically, across a member's physical copy, EPUB, and audiobook. Any design that lets a club set one Checkpoint and have it apply uniformly across formats needs either (a) a human-curated mapping from that Checkpoint to a location in each format the club's members actually use, or (b) a Checkpoint defined in a coarser, more format-robust unit (e.g. percentage-through-work, or a narrative description a human maps per-format) rather than a raw chapter number.

3. **A standard vocabulary for separating "the story" from "the copy" already exists and is actively used by adjacent systems**: FRBR/IFLA LRM's Work/Expression/Manifestation/Item split (Work = abstract content; Expression = the specific realization, explicitly including the print-vs-audio distinction — "change in form... from written notation to spoken word results in a new expression"; Manifestation = the physical/file-specific carrier that owns pagination); schema.org's `workExample`/`exampleOfWork` pair, which is schema.org's own adaptation of this same FRBR idea; and Open Library's Work/Edition REST resources, which implement a simplified two-level version of it. **Implication:** the app has a ready-made, precedented conceptual shape available — "Book" (or "Work") as the edition-independent thing a Checkpoint's narrative description belongs to, versus "Edition" (physical/EPUB/audio) as the thing a Member's raw position (page number, EPUB CFI, audio timestamp) belongs to — without having to invent that split from scratch. Using it doesn't solve the mapping problem in point 2 above, but it does give a defensible place to put a Checkpoint's canonical definition (once, on the Work) versus a per-format offset/mapping (many, on the Edition).

4. **EPUB CFI is confirmed, from the primary IDPF spec, to be real prior art for "address a location without relying on page number" — but it is EPUB-only.** It cannot address a position in a print book or an audio file, and the spec gives no mechanism for translating a CFI computed against one EPUB build into a CFI valid against a structurally different EPUB build of the same title. **Implication:** CFI (or an app-specific analogue of the same idea — a structural path into the text rather than a page number) could plausibly be used as the *within-ebook* representation of a Member's position for EPUB readers specifically, but it cannot by itself serve as the shared, cross-format position unit the app needs; something else (e.g., a percentage, or a mapping maintained per Edition) is required to compare an EPUB-CFI position against a print page number or an audio timestamp.

5. **No first-party documentation was found, from any of the apps checked, of a general solution to cross-edition/cross-format progress comparison.** Goodreads offers a page-number/percentage toggle (an edition-independent unit is available, but the app does not appear to reconcile *between* editions on the user's behalf — this is inferred from the toggle's existence, not from directly-read explanatory text, since that page could not be fetched). StoryGraph's own changelog documents plain page-count tracking with no cross-edition handling mentioned. Fable and Literal.club have no discoverable first-party documentation of their internal approach at all. Amazon's Whispersync for Voice is confirmed to exist as a marketed feature, but its underlying mechanism could not be verified from a directly-read Amazon page (persistent 503s). **Implication:** there is no existing first-party design this app can simply copy for the cross-edition comparison problem specifically — percentage-of-book is the one edition-agnostic unit that recurs across the apps checked (Goodreads, StoryGraph, Literal), which is a documented existing pattern the app could adopt or deliberately diverge from, but none of the primary sources describe percentage (or any other unit) as solving the *comparison-against-a-club-set-Checkpoint* problem this app specifically needs to solve — that appears to be open design space, not something to source from prior art.

---

## Sources consulted but not (or only partially) accessible

For transparency, these were attempted and either failed or returned insufficient content, and no secondary source was substituted in their place within the claims above:

- `loc.gov/marc/bibliographic/bd505.html` — 403 Forbidden (used OCLC's parallel page instead, cited above).
- `isbndb.com/apidocs/v2` — 403 Forbidden. ISBNdb's schema is not verified here.
- `amazon.com/gp/help/customer/display.html` (multiple node IDs, for "Real Page Numbers" and Whispersync for Voice consumer pages) — 503 Service Unavailable on every attempt.
- `goodreads.com/help/show/212-...` → redirected to `help.goodreads.com/s/article/...1553870933549` — 404 on the redirected URL.
- `www.ifla.org/files/assets/cataloguing/isbd/OtherDocumentation/resource-wemi.pdf` and `loc.gov/catdir/cpso/frbreng.pdf` — both 403 Forbidden (the IFLA LRM PDF fetched successfully from `repository.ifla.org` was used instead and is the authoritative, current version of the same model).
- Literal.club — no first-party help/FAQ page could be located via search at all.
