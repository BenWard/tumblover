# Tumblover

PHP script to take URLs from an RSS feed (such as saved items from Google
Reader, Fever, Delicious and so on), identify Tumblr URLs, and then uses the
Tumblr API to mark the item as loved back on Tumblr itself.

Uses Tumblr's Twitter API to fave content, since the official API doesn't
document like/unlike.

Share the love.

## Changelog

### v0.1.0

* First working version.
* Takes the content of an RSS feed, finds posts likely to be from Tumblr (with
  a /post/[0-9]+ url structure), maps them to recent posts on the user's
  Tumblr Dashboard via the Tumblr-Twitter API, and where a match is found,
  "likes" the post on Tumblr

## Known Issues

* Because Tumblr uses different post IDs in the Twitter API from the Tumblr
  API, it is necessary to map them. The mapping requires loading a sensible
  number of recent posts from the Tumblr dashboard in Twitter form, resolving
  the Short-URL that Tumblr appends to each post into a full Tumblr URL (by
  following 301-redirects), and then extracting the Tumblr ID from the full
  URL. This is slow, and in v0.5.0 the results of this mapping are not cached.
* A future version will cache the Twitter/Tumblr ID mappings, and only request
  newer posts to reduce processing.

## License

This source code is open, under a Simplified BSD license. Have fun.

Copyright (c) 2009, Ben Ward <http://benward.me>
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:

Redistributions of source code must retain the above copyright notice, this
list of conditions and the following disclaimer.
Redistributions in binary form must reproduce the above copyright notice,
this list of conditions and the following disclaimer in the documentation
and/or other materials provided with the distribution.

Neither the name of Ben Ward nor the names of its contributors may be used
to endorse or promote products derived from this software without specific
prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
POSSIBILITY OF SUCH DAMAGE.