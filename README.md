YiiXL - The Yii Extension Library of Doom!
===============================
Thanks for checking out *YiiXL*! This is brand-spanking-new version of the Pogostick Yii Extensions Library. We're re-branding the library as YiiXL and rewriting some core features. We considered a major version upgrade but it could possibly affect too many people using these extensions.

Why the goofy name you ask? Because the word *Yii* is just too damned hard to squeeze into a catchy acronym or phrase. I said screw it and came up with YiiXL. The "<i>of Doom!</i>" part is just for sensationalism. I'm tired of not having fun any more. lol

If you are looking for the original library, it can be found here: <http://github.com/Pogostick/ps-yii-extensions>

Features
========

* Quicker to code repetitive tasks
* All setters return $this for easy chaining
* Streamlined the entire autoloader sequence to lessen burden on configuration

* Behaviors: Support for calling behavior methods from owner object
* Extensible: Awesome mix-in framework allows for easy add-ons



Notes
=====
The first release was back in 2009 with several additional releases culminating with the award-winning v1.0.6. I've done quite
a bit of work with the library since then, and I've found many things that were in my initial design, that I thought were cool,
have become either antiquated, unnecessary, or annoying.

With the advent of _Yii v1.1.6_, a few of my features have been incorporated into the
code base (behavior methods and properties being accessible by the owner for example).

In addition, I've built an entire form framework that is poorly documented and sits alongside the original forms system I devised. This new
framework is much easier to use. So I want to get that into the library as well.

So, why the diatribe? Well, it's like this...

The original library made available, and extensive use of, runtime-defined object properties. While, again, I thought it was cool at the time;
has now become annoying and has two drawbacks I dislike. The first being that you cannot use constructs (empty() for instance) with them.
The second being the inability for IDEs to pull up phpdoc on these properties. Two bogus things IMHO.

This new version, when released will _NOT BE_ backward-compatible with the Pogostick Yii Extension Library. While some aspects of the library will function the same, I cannot (and will not) guarantee they will work.

Well, if you've read this far, thanks, you're awesome! I enjoy building tools like this and it warms my cockles to know that there are people out there
in the community that can (and do) benefit from my work. I suppose it's my little karma contribution.

So, without further ado, here's some really short and probably confusing documentation.

Installation
============
[todo]

Requirements
============
* PHP v5.3+
 YiiXL requires PHP v5.3+. While it will work somewhat with version 5.2x, some functionality maybe hosed and I have no idea what. So use at your own risk.

* Yii v1.1+
 YiiXL extends the Yii Framework and, of course (duh!), requires it to operate properly.

* Mad Skillz
