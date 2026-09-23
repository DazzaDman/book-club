# Book Club

A mobile-first app where friends form invite-only clubs, nominate and vote on what to read next, track their reading progress, and post discussion notes.

## Language

**Club**:
An invite-only group of friends who read books together on a recurring cadence (default monthly, configurable per club).
_Avoid_: Group, community

**Member**:
A person who belongs to a Club. A person may belong to more than one Club.
_Avoid_: User, participant

**Nomination**:
A Member's proposal of a candidate — a Book or a Meetup Proposal — for the Club to vote on during a Cycle.
_Avoid_: Suggestion, pick

**Vote**:
A Member's ranked top-3 preference among a round's Nominations for a given subject (Book or Meetup Proposal), scored across the whole pool — not just the Member's own Nominations. Scored by Borda count (1st = 3 points, 2nd = 2, 3rd = 1) summed across all Members; highest total wins. Ties break first by most 1st-place rankings, then randomly.
_Avoid_: Rating, poll response

**Meetup Proposal**:
A Nomination of a physical place for one of a Cycle's in-person discussion meetups, decided by the same ranked Vote as Book. A Cycle may have several Meetups, scheduled one at a time as each prior one wraps. Once a Meetup Proposal wins, its nominator sets the meetup's date, time, and Checkpoint, which Members RSVP to rather than vote on.
_Avoid_: Event, gathering

**RSVP**:
A Member's confirmation of attendance (going / not going) to a Cycle's winning Meetup Proposal.

**Cycle-End Vote**:
A yes/no ballot, callable by any Member, to close a Cycle early. Passes only with a majority of all Club Members, not just those who vote.

**Reading Progress**:
A Member's self-reported position in the Club's current Book, visible to the rest of the Club, expressed as a structural marker comparable to a Checkpoint rather than a page number (which differs across physical, ebook, and audiobook editions). Exact unit undecided — under research.
_Avoid_: Bookmark, page number, percentage

**Discussion Note**:
A Member's written comment about the current book, hidden from other Members until their own Reading Progress reaches the point the note pertains to.
_Avoid_: Comment, post, review

**Cycle**:
The span during which a Club works through one Book, from nomination until the Club closes it. Contains one or more Meetups; has no fixed calendar length, running however long the Club actually takes.
_Avoid_: Round, month, season

**Checkpoint**:
A loosely-tracked marker (a chapter, part, or narrative arc) that a Meetup sets as how far Members should have read by then. Set unilaterally by the Meetup's winning nominator, alongside its date and time — not voted on, since the specifics get worked out in person.

**Book**:
A catalog entry (from an external book catalog, with freeform fallback if unmatched) that Members nominate and a Club reads together during a Cycle.

**Owner**:
The Member who created a Club, with sole authority over housekeeping (name, cadence, invites, membership removal). Nomination and voting remain peer-equal among all Members regardless of Owner status. An Owner may not leave a Club until they nominate another Member as the new Owner — ownership transfers to that Member the moment the original Owner leaves.
_Avoid_: Admin, moderator
