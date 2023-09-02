{
  composerEnv,
  fetchurl,
  fetchgit ? null,
  fetchhg ? null,
  fetchsvn ? null,
  noDev ? false,
}: let
  packages = {
    "barryvdh/laravel-dompdf" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "barryvdh-laravel-dompdf-9843d2be423670fb434f4c978b3c0f4dd92c87a6";
        src = fetchurl {
          url = "https://api.github.com/repos/barryvdh/laravel-dompdf/zipball/9843d2be423670fb434f4c978b3c0f4dd92c87a6";
          sha256 = "1b7j7rnba50ibsnjzxz3bcnpcii51qrin5p0ivi0bzm57xhvns9s";
        };
      };
    };
    "brick/math" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "brick-math-0ad82ce168c82ba30d1c01ec86116ab52f589478";
        src = fetchurl {
          url = "https://api.github.com/repos/brick/math/zipball/0ad82ce168c82ba30d1c01ec86116ab52f589478";
          sha256 = "04kqy1hqvp4634njjjmhrc2g828d69sk6q3c55bpqnnmsqf154yb";
        };
      };
    };
    "darkaonline/l5-swagger" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "darkaonline-l5-swagger-02348149f1833c63bf52764838d5659507857394";
        src = fetchurl {
          url = "https://api.github.com/repos/DarkaOnLine/L5-Swagger/zipball/02348149f1833c63bf52764838d5659507857394";
          sha256 = "0d892jsvmwipq42l5wmdq6nak3d3q29zc02cvm4pm7q0g6h88c2f";
        };
      };
    };
    "defuse/php-encryption" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "defuse-php-encryption-f53396c2d34225064647a05ca76c1da9d99e5828";
        src = fetchurl {
          url = "https://api.github.com/repos/defuse/php-encryption/zipball/f53396c2d34225064647a05ca76c1da9d99e5828";
          sha256 = "1g4mnnw9nmg1v8zq04d56v5n4m6vr3rsjbqdbny9d2f4c8xd4pqz";
        };
      };
    };
    "dflydev/dot-access-data" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "dflydev-dot-access-data-f41715465d65213d644d3141a6a93081be5d3549";
        src = fetchurl {
          url = "https://api.github.com/repos/dflydev/dflydev-dot-access-data/zipball/f41715465d65213d644d3141a6a93081be5d3549";
          sha256 = "1vgbjrq8qh06r26y5nlxfin4989r3h7dib1jifb2l3cjdn1r5bmj";
        };
      };
    };
    "doctrine/annotations" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "doctrine-annotations-e157ef3f3124bbf6fe7ce0ffd109e8a8ef284e7f";
        src = fetchurl {
          url = "https://api.github.com/repos/doctrine/annotations/zipball/e157ef3f3124bbf6fe7ce0ffd109e8a8ef284e7f";
          sha256 = "1lf9y10schsh11185xgfnwn91i77njymz3zv43xh4qcyjq5fjg32";
        };
      };
    };
    "doctrine/cache" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "doctrine-cache-1ca8f21980e770095a31456042471a57bc4c68fb";
        src = fetchurl {
          url = "https://api.github.com/repos/doctrine/cache/zipball/1ca8f21980e770095a31456042471a57bc4c68fb";
          sha256 = "1p8ia9g3mqz71bv4x8q1ng1fgcidmyksbsli1fjbialpgjk9k1ss";
        };
      };
    };
    "doctrine/dbal" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "doctrine-dbal-63646ffd71d1676d2f747f871be31b7e921c7864";
        src = fetchurl {
          url = "https://api.github.com/repos/doctrine/dbal/zipball/63646ffd71d1676d2f747f871be31b7e921c7864";
          sha256 = "0qqiq46983m5c4w0i59z2ik1v69slglr8nrj5p2zcpxq9zmczbkj";
        };
      };
    };
    "doctrine/deprecations" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "doctrine-deprecations-612a3ee5ab0d5dd97b7cf3874a6efe24325efac3";
        src = fetchurl {
          url = "https://api.github.com/repos/doctrine/deprecations/zipball/612a3ee5ab0d5dd97b7cf3874a6efe24325efac3";
          sha256 = "078w4k0xdywyb44caz5grbcbxsi87iy13g7a270rs9g5f0p245fi";
        };
      };
    };
    "doctrine/event-manager" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "doctrine-event-manager-750671534e0241a7c50ea5b43f67e23eb5c96f32";
        src = fetchurl {
          url = "https://api.github.com/repos/doctrine/event-manager/zipball/750671534e0241a7c50ea5b43f67e23eb5c96f32";
          sha256 = "1inhh3k8ai8d6rhx5xsbdx0ifc3yjjfrahi0cy1npz9nx3383cfh";
        };
      };
    };
    "doctrine/inflector" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "doctrine-inflector-f9301a5b2fb1216b2b08f02ba04dc45423db6bff";
        src = fetchurl {
          url = "https://api.github.com/repos/doctrine/inflector/zipball/f9301a5b2fb1216b2b08f02ba04dc45423db6bff";
          sha256 = "1kdq3sbwrzwprxr36cdw9m1zlwn15c0nz6i7mw0sq9mhnd2sgbrb";
        };
      };
    };
    "doctrine/lexer" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "doctrine-lexer-84a527db05647743d50373e0ec53a152f2cde568";
        src = fetchurl {
          url = "https://api.github.com/repos/doctrine/lexer/zipball/84a527db05647743d50373e0ec53a152f2cde568";
          sha256 = "1wn3p8vjq3hqzn0k6dmwxdj2ykwk3653h5yw7a57avz9qkb86z70";
        };
      };
    };
    "dompdf/dompdf" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "dompdf-dompdf-e8d2d5e37e8b0b30f0732a011295ab80680d7e85";
        src = fetchurl {
          url = "https://api.github.com/repos/dompdf/dompdf/zipball/e8d2d5e37e8b0b30f0732a011295ab80680d7e85";
          sha256 = "0a2qk57c3qwg7j8gp1hwyd8y8dwm5pb8lg1npb49sijig8kyjlv3";
        };
      };
    };
    "dragonmantank/cron-expression" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "dragonmantank-cron-expression-adfb1f505deb6384dc8b39804c5065dd3c8c8c0a";
        src = fetchurl {
          url = "https://api.github.com/repos/dragonmantank/cron-expression/zipball/adfb1f505deb6384dc8b39804c5065dd3c8c8c0a";
          sha256 = "1gw2bnsh8ca5plfpyyyz1idnx7zxssg6fbwl7niszck773zrm5ca";
        };
      };
    };
    "egulias/email-validator" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "egulias-email-validator-3a85486b709bc384dae8eb78fb2eec649bdb64ff";
        src = fetchurl {
          url = "https://api.github.com/repos/egulias/EmailValidator/zipball/3a85486b709bc384dae8eb78fb2eec649bdb64ff";
          sha256 = "1vbwd4fgg6910pfy0dpzkaf5djwzpx5nqr43hy2qpmkp11mkbbxw";
        };
      };
    };
    "firebase/php-jwt" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "firebase-php-jwt-5dbc8959427416b8ee09a100d7a8588c00fb2e26";
        src = fetchurl {
          url = "https://api.github.com/repos/firebase/php-jwt/zipball/5dbc8959427416b8ee09a100d7a8588c00fb2e26";
          sha256 = "0iaq0ywhryjjfxn2l0pyn14r128byslrcrfpzygmj2b6r6kc5mz8";
        };
      };
    };
    "fruitcake/php-cors" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "fruitcake-php-cors-58571acbaa5f9f462c9c77e911700ac66f446d4e";
        src = fetchurl {
          url = "https://api.github.com/repos/fruitcake/php-cors/zipball/58571acbaa5f9f462c9c77e911700ac66f446d4e";
          sha256 = "18xm69q4dk9zqfwgp938y2byhlyy9lr5x5qln4k2mg8cq8xr2sm1";
        };
      };
    };
    "graham-campbell/result-type" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "graham-campbell-result-type-672eff8cf1d6fe1ef09ca0f89c4b287d6a3eb831";
        src = fetchurl {
          url = "https://api.github.com/repos/GrahamCampbell/Result-Type/zipball/672eff8cf1d6fe1ef09ca0f89c4b287d6a3eb831";
          sha256 = "156zbfs19r9g543phlpjwhqin3k2x4dsvr5p0wk7rk4j0wwp8l2v";
        };
      };
    };
    "guzzlehttp/guzzle" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "guzzlehttp-guzzle-1110f66a6530a40fe7aea0378fe608ee2b2248f9";
        src = fetchurl {
          url = "https://api.github.com/repos/guzzle/guzzle/zipball/1110f66a6530a40fe7aea0378fe608ee2b2248f9";
          sha256 = "0zl8qg9c5nvfdwv0dc9rr58lzcnslczkkfw2mlvxc3sbgv4zw9w3";
        };
      };
    };
    "guzzlehttp/promises" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "guzzlehttp-promises-111166291a0f8130081195ac4556a5587d7f1b5d";
        src = fetchurl {
          url = "https://api.github.com/repos/guzzle/promises/zipball/111166291a0f8130081195ac4556a5587d7f1b5d";
          sha256 = "17d50in3wq62y8gcgadiimn9s2mc90xvil5g851l9y2k0c4y31s2";
        };
      };
    };
    "guzzlehttp/psr7" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "guzzlehttp-psr7-be45764272e8873c72dbe3d2edcfdfcc3bc9f727";
        src = fetchurl {
          url = "https://api.github.com/repos/guzzle/psr7/zipball/be45764272e8873c72dbe3d2edcfdfcc3bc9f727";
          sha256 = "1x61sn6srz979p0y6cvbzl3m4dvskgqs68zrhmqpymjdj7b45acn";
        };
      };
    };
    "guzzlehttp/uri-template" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "guzzlehttp-uri-template-61bf437fc2197f587f6857d3ff903a24f1731b5d";
        src = fetchurl {
          url = "https://api.github.com/repos/guzzle/uri-template/zipball/61bf437fc2197f587f6857d3ff903a24f1731b5d";
          sha256 = "1i6k0bk30vz4aqsgbj2k4r6kwhmp26qqdqzic58ggdhc7nbyq56d";
        };
      };
    };
    "intervention/image" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "intervention-image-04be355f8d6734c826045d02a1079ad658322dad";
        src = fetchurl {
          url = "https://api.github.com/repos/Intervention/image/zipball/04be355f8d6734c826045d02a1079ad658322dad";
          sha256 = "1cbg43hm2jgwb7gm1r9xcr4cpx8ng1zr93zx6shk9xhjlssnv0bx";
        };
      };
    };
    "jaybizzle/crawler-detect" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "jaybizzle-crawler-detect-97e9fe30219e60092e107651abb379a38b342921";
        src = fetchurl {
          url = "https://api.github.com/repos/JayBizzle/Crawler-Detect/zipball/97e9fe30219e60092e107651abb379a38b342921";
          sha256 = "0ywqamhyilrlb1sli00i2gnaw2hyjpbb9pkxb8nxx7dr1a4v8x7q";
        };
      };
    };
    "jenssegers/agent" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "jenssegers-agent-daa11c43729510b3700bc34d414664966b03bffe";
        src = fetchurl {
          url = "https://api.github.com/repos/jenssegers/agent/zipball/daa11c43729510b3700bc34d414664966b03bffe";
          sha256 = "0f0wy69w9mdsajfgriwlnpqhqxp83q44p6ggcd6h1bi8ri3h0897";
        };
      };
    };
    "laravel/framework" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "laravel-framework-b8557e4a708a1bd2bc8229bd53feecfa2ac1c6fb";
        src = fetchurl {
          url = "https://api.github.com/repos/laravel/framework/zipball/b8557e4a708a1bd2bc8229bd53feecfa2ac1c6fb";
          sha256 = "0bccnkkg608jfw21jr9j8pfddlcsr6b5iv642vlg35zdlp54jhal";
        };
      };
    };
    "laravel/passport" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "laravel-passport-401836130d46c94138a637ada29f9e5b2bf053b6";
        src = fetchurl {
          url = "https://api.github.com/repos/laravel/passport/zipball/401836130d46c94138a637ada29f9e5b2bf053b6";
          sha256 = "1dnqbhsvff9l7xsj1fx6m5nmz1pf8cfh8xxykg19647v2q9b12fr";
        };
      };
    };
    "laravel/prompts" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "laravel-prompts-d880a909df144a4bf5760ebd09aba114f79d9adc";
        src = fetchurl {
          url = "https://api.github.com/repos/laravel/prompts/zipball/d880a909df144a4bf5760ebd09aba114f79d9adc";
          sha256 = "10jmqp5d7zz90wha3sdy7j6cq628fsw3z93yfrygd07g7ihglbks";
        };
      };
    };
    "laravel/serializable-closure" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "laravel-serializable-closure-e5a3057a5591e1cfe8183034b0203921abe2c902";
        src = fetchurl {
          url = "https://api.github.com/repos/laravel/serializable-closure/zipball/e5a3057a5591e1cfe8183034b0203921abe2c902";
          sha256 = "0sjcn7w31x14slfj2mqs32kj62ay86i47i441p5cg3ajw9kjb6iy";
        };
      };
    };
    "laravel/socialite" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "laravel-socialite-9989b4530331597fae811bca240bf4e8f15e804b";
        src = fetchurl {
          url = "https://api.github.com/repos/laravel/socialite/zipball/9989b4530331597fae811bca240bf4e8f15e804b";
          sha256 = "1v5qcghhdy0swk00l13pmifa2nwbpd5cl3ripraihwylwky7fls5";
        };
      };
    };
    "laravel/tinker" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "laravel-tinker-04a2d3bd0d650c0764f70bf49d1ee39393e4eb10";
        src = fetchurl {
          url = "https://api.github.com/repos/laravel/tinker/zipball/04a2d3bd0d650c0764f70bf49d1ee39393e4eb10";
          sha256 = "06rivrmcf8m8hm4vn9s7wwpfmgl89p73b78dm0qx26rs0lpr36p0";
        };
      };
    };
    "laravel/ui" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "laravel-ui-a58ec468db4a340b33f3426c778784717a2c144b";
        src = fetchurl {
          url = "https://api.github.com/repos/laravel/ui/zipball/a58ec468db4a340b33f3426c778784717a2c144b";
          sha256 = "0qrfr7rbi5b90inx3xf5yy5p9h38rs9b2567p2vh3711w4kqjmqd";
        };
      };
    };
    "lcobucci/clock" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "lcobucci-clock-039ef98c6b57b101d10bd11d8fdfda12cbd996dc";
        src = fetchurl {
          url = "https://api.github.com/repos/lcobucci/clock/zipball/039ef98c6b57b101d10bd11d8fdfda12cbd996dc";
          sha256 = "03hlh6vl04jhhjkk6ps4wikypkg849wq8pdg221359l82ivz16hg";
        };
      };
    };
    "lcobucci/jwt" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "lcobucci-jwt-47bdb0e0b5d00c2f89ebe33e7e384c77e84e7c34";
        src = fetchurl {
          url = "https://api.github.com/repos/lcobucci/jwt/zipball/47bdb0e0b5d00c2f89ebe33e7e384c77e84e7c34";
          sha256 = "0bkkf98iflgdpryxm270wwgzw9id627h2iszjgw7ddkibn14lgq3";
        };
      };
    };
    "league/commonmark" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "league-commonmark-d44a24690f16b8c1808bf13b1bd54ae4c63ea048";
        src = fetchurl {
          url = "https://api.github.com/repos/thephpleague/commonmark/zipball/d44a24690f16b8c1808bf13b1bd54ae4c63ea048";
          sha256 = "1qx99m1qa2g3l6r2fim3rak6qh28zjj8sqjj86nq743dm3yszygw";
        };
      };
    };
    "league/config" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "league-config-754b3604fb2984c71f4af4a9cbe7b57f346ec1f3";
        src = fetchurl {
          url = "https://api.github.com/repos/thephpleague/config/zipball/754b3604fb2984c71f4af4a9cbe7b57f346ec1f3";
          sha256 = "0yjb85cd0qa0mra995863dij2hmcwk9x124vs8lrwiylb0l3mn8s";
        };
      };
    };
    "league/event" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "league-event-d2cc124cf9a3fab2bb4ff963307f60361ce4d119";
        src = fetchurl {
          url = "https://api.github.com/repos/thephpleague/event/zipball/d2cc124cf9a3fab2bb4ff963307f60361ce4d119";
          sha256 = "1fc8aj0mpbrnh3b93gn8pypix28nf2gfvi403kfl7ibh5iz6ds5l";
        };
      };
    };
    "league/flysystem" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "league-flysystem-a141d430414fcb8bf797a18716b09f759a385bed";
        src = fetchurl {
          url = "https://api.github.com/repos/thephpleague/flysystem/zipball/a141d430414fcb8bf797a18716b09f759a385bed";
          sha256 = "0w476nkv4izdrh8dn4g58lrqnfwrp8ijhj6fj8d8cpvr81kq0wiv";
        };
      };
    };
    "league/flysystem-local" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "league-flysystem-local-543f64c397fefdf9cfeac443ffb6beff602796b3";
        src = fetchurl {
          url = "https://api.github.com/repos/thephpleague/flysystem-local/zipball/543f64c397fefdf9cfeac443ffb6beff602796b3";
          sha256 = "1f44jgjip7pgnjafwlazmbv9jap3xsw3jfzhgakbsa4bkx3aavr2";
        };
      };
    };
    "league/glide" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "league-glide-2ff92c8f1edc80b74e2d3c5efccfc7223f74d407";
        src = fetchurl {
          url = "https://api.github.com/repos/thephpleague/glide/zipball/2ff92c8f1edc80b74e2d3c5efccfc7223f74d407";
          sha256 = "1fhgp24i44p5a5r0axajjl2969q962200dn2y3z1dkf9v4hf9i3n";
        };
      };
    };
    "league/mime-type-detection" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "league-mime-type-detection-a6dfb1194a2946fcdc1f38219445234f65b35c96";
        src = fetchurl {
          url = "https://api.github.com/repos/thephpleague/mime-type-detection/zipball/a6dfb1194a2946fcdc1f38219445234f65b35c96";
          sha256 = "14yylg4lyyc522kpzlcby1zhfs01d8xrcc4rqvyink0ry8az12jj";
        };
      };
    };
    "league/oauth1-client" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "league-oauth1-client-d6365b901b5c287dd41f143033315e2f777e1167";
        src = fetchurl {
          url = "https://api.github.com/repos/thephpleague/oauth1-client/zipball/d6365b901b5c287dd41f143033315e2f777e1167";
          sha256 = "0hkh8l7884g8ssja1biwfb59x0jj951lwk6kmiacjqvyvzs07qmx";
        };
      };
    };
    "league/oauth2-server" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "league-oauth2-server-ab7714d073844497fd222d5d0a217629089936bc";
        src = fetchurl {
          url = "https://api.github.com/repos/thephpleague/oauth2-server/zipball/ab7714d073844497fd222d5d0a217629089936bc";
          sha256 = "1p4lvibdfi458bv778qzbah3b1lkhdvd9hiws040ky8jizfs6c2g";
        };
      };
    };
    "league/uri" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "league-uri-c0bf6dfa86b7804fe870b3f3d9c653e35a2c9e3e";
        src = fetchurl {
          url = "https://api.github.com/repos/thephpleague/uri/zipball/c0bf6dfa86b7804fe870b3f3d9c653e35a2c9e3e";
          sha256 = "0fl79j6wglhr4ndfda5afkpyfyx1m5bgabfaj2psq9j5ns5dpn0w";
        };
      };
    };
    "league/uri-interfaces" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "league-uri-interfaces-c3ea9306c67c9a1a72312705e8adfcb9cf167310";
        src = fetchurl {
          url = "https://api.github.com/repos/thephpleague/uri-interfaces/zipball/c3ea9306c67c9a1a72312705e8adfcb9cf167310";
          sha256 = "1wj7xiabcgic4yyyhi6g51wlxj18f5gk5rwjpgd3s2f5pmq1321q";
        };
      };
    };
    "masterminds/html5" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "masterminds-html5-f47dcf3c70c584de14f21143c55d9939631bc6cf";
        src = fetchurl {
          url = "https://api.github.com/repos/Masterminds/html5-php/zipball/f47dcf3c70c584de14f21143c55d9939631bc6cf";
          sha256 = "1n2xiyxqmxk9g34wn1lg2yyivwg2ry8iqk8m7g2432gm97rmyb20";
        };
      };
    };
    "mobiledetect/mobiledetectlib" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "mobiledetect-mobiledetectlib-fc9cccd4d3706d5a7537b562b59cc18f9e4c0cb1";
        src = fetchurl {
          url = "https://api.github.com/repos/serbanghita/Mobile-Detect/zipball/fc9cccd4d3706d5a7537b562b59cc18f9e4c0cb1";
          sha256 = "1qmkrbdrfnxgd7lcgw7g30r8qc6yg1c9lkdam54zhgxhcc2ryxqs";
        };
      };
    };
    "monolog/monolog" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "monolog-monolog-e2392369686d420ca32df3803de28b5d6f76867d";
        src = fetchurl {
          url = "https://api.github.com/repos/Seldaek/monolog/zipball/e2392369686d420ca32df3803de28b5d6f76867d";
          sha256 = "1cs9gx2qgggzzzn41858h2l2v7rrlnysxjxflmdm5ajyxw50sy2n";
        };
      };
    };
    "nesbot/carbon" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "nesbot-carbon-4308217830e4ca445583a37d1bf4aff4153fa81c";
        src = fetchurl {
          url = "https://api.github.com/repos/briannesbitt/Carbon/zipball/4308217830e4ca445583a37d1bf4aff4153fa81c";
          sha256 = "1ycjj9d107nbv390qqm9phmhvc1slxl0pa2dzf8lhmcif58863vb";
        };
      };
    };
    "nette/schema" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "nette-schema-c9ff517a53903b3d4e29ec547fb20feecb05b8ab";
        src = fetchurl {
          url = "https://api.github.com/repos/nette/schema/zipball/c9ff517a53903b3d4e29ec547fb20feecb05b8ab";
          sha256 = "1b7xhxdm2hxm5m0b3d2sv2rhw5986bqkxfl9k7mqdfka78mpvmlx";
        };
      };
    };
    "nette/utils" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "nette-utils-9124157137da01b1f5a5a22d6486cb975f26db7e";
        src = fetchurl {
          url = "https://api.github.com/repos/nette/utils/zipball/9124157137da01b1f5a5a22d6486cb975f26db7e";
          sha256 = "0ly27z4m2w9h24kssjc5x4rhksw5hd9nwjzxnzmxrz7kvh7068gd";
        };
      };
    };
    "nicmart/tree" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "nicmart-tree-c55ba47c64a3cb7454c22e6d630729fc2aab23ff";
        src = fetchurl {
          url = "https://api.github.com/repos/nicmart/Tree/zipball/c55ba47c64a3cb7454c22e6d630729fc2aab23ff";
          sha256 = "1hf0b4qv347rqhzzgcvvimq2qw3q9kfzlpcarcxksn955287byd3";
        };
      };
    };
    "nikic/php-parser" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "nikic-php-parser-a6303e50c90c355c7eeee2c4a8b27fe8dc8fef1d";
        src = fetchurl {
          url = "https://api.github.com/repos/nikic/PHP-Parser/zipball/a6303e50c90c355c7eeee2c4a8b27fe8dc8fef1d";
          sha256 = "0a5a6fzgvcgxn5kc1mxa5grxmm8c1ax91pjr3gxpkji7nyc1zh1y";
        };
      };
    };
    "nunomaduro/termwind" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "nunomaduro-termwind-8ab0b32c8caa4a2e09700ea32925441385e4a5dc";
        src = fetchurl {
          url = "https://api.github.com/repos/nunomaduro/termwind/zipball/8ab0b32c8caa4a2e09700ea32925441385e4a5dc";
          sha256 = "1g75vpq7014s5yd6bvj78b88ia31slkikdhjv8iprz987qm5dnl7";
        };
      };
    };
    "nyholm/psr7" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "nyholm-psr7-3cb4d163b58589e47b35103e8e5e6a6a475b47be";
        src = fetchurl {
          url = "https://api.github.com/repos/Nyholm/psr7/zipball/3cb4d163b58589e47b35103e8e5e6a6a475b47be";
          sha256 = "1wbg5fcqkv8bg1ll0ndxp3jmi353sz6cd6gzdldbh35p1b2mp4jm";
        };
      };
    };
    "paragonie/constant_time_encoding" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "paragonie-constant_time_encoding-58c3f47f650c94ec05a151692652a868995d2938";
        src = fetchurl {
          url = "https://api.github.com/repos/paragonie/constant_time_encoding/zipball/58c3f47f650c94ec05a151692652a868995d2938";
          sha256 = "0i9km0lzvc7df9758fm1p3y0679pzvr5m9x3mrz0d2hxlppsm764";
        };
      };
    };
    "paragonie/random_compat" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "paragonie-random_compat-996434e5492cb4c3edcb9168db6fbb1359ef965a";
        src = fetchurl {
          url = "https://api.github.com/repos/paragonie/random_compat/zipball/996434e5492cb4c3edcb9168db6fbb1359ef965a";
          sha256 = "0ky7lal59dihf969r1k3pb96ql8zzdc5062jdbg69j6rj0scgkyx";
        };
      };
    };
    "phenx/php-font-lib" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "phenx-php-font-lib-dd448ad1ce34c63d09baccd05415e361300c35b4";
        src = fetchurl {
          url = "https://api.github.com/repos/dompdf/php-font-lib/zipball/dd448ad1ce34c63d09baccd05415e361300c35b4";
          sha256 = "0l20inbvipjdg9fdd32v8b7agjyvcs0rpqswcylld64vbm2sf3il";
        };
      };
    };
    "phenx/php-svg-lib" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "phenx-php-svg-lib-76876c6cf3080bcb6f249d7d59705108166a6685";
        src = fetchurl {
          url = "https://api.github.com/repos/dompdf/php-svg-lib/zipball/76876c6cf3080bcb6f249d7d59705108166a6685";
          sha256 = "0bjynrs81das9f9jwd5jgsxx9gjv2m6c0mkvlgx4w1f4pgbvwsf5";
        };
      };
    };
    "phpoption/phpoption" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "phpoption-phpoption-dd3a383e599f49777d8b628dadbb90cae435b87e";
        src = fetchurl {
          url = "https://api.github.com/repos/schmittjoh/php-option/zipball/dd3a383e599f49777d8b628dadbb90cae435b87e";
          sha256 = "029gpfa66hwg395jvf7swcvrj085wsw5fw6041nrl5kbc36fvwlb";
        };
      };
    };
    "phpseclib/phpseclib" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "phpseclib-phpseclib-4580645d3fc05c189024eb3b834c6c1e4f0f30a1";
        src = fetchurl {
          url = "https://api.github.com/repos/phpseclib/phpseclib/zipball/4580645d3fc05c189024eb3b834c6c1e4f0f30a1";
          sha256 = "0v3c7n9h99pw4f03bfxjsgni7wpq7xr47nw2hf2hq8yjndw19n3p";
        };
      };
    };
    "psr/cache" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "psr-cache-aa5030cfa5405eccfdcb1083ce040c2cb8d253bf";
        src = fetchurl {
          url = "https://api.github.com/repos/php-fig/cache/zipball/aa5030cfa5405eccfdcb1083ce040c2cb8d253bf";
          sha256 = "07rnyjwb445sfj30v5ny3gfsgc1m7j7cyvwjgs2cm9slns1k1ml8";
        };
      };
    };
    "psr/clock" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "psr-clock-e41a24703d4560fd0acb709162f73b8adfc3aa0d";
        src = fetchurl {
          url = "https://api.github.com/repos/php-fig/clock/zipball/e41a24703d4560fd0acb709162f73b8adfc3aa0d";
          sha256 = "0wz5b8hgkxn3jg88cb3901hj71axsj0fil6pwl413igghch6i8kj";
        };
      };
    };
    "psr/container" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "psr-container-c71ecc56dfe541dbd90c5360474fbc405f8d5963";
        src = fetchurl {
          url = "https://api.github.com/repos/php-fig/container/zipball/c71ecc56dfe541dbd90c5360474fbc405f8d5963";
          sha256 = "1mvan38yb65hwk68hl0p7jymwzr4zfnaxmwjbw7nj3rsknvga49i";
        };
      };
    };
    "psr/event-dispatcher" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "psr-event-dispatcher-dbefd12671e8a14ec7f180cab83036ed26714bb0";
        src = fetchurl {
          url = "https://api.github.com/repos/php-fig/event-dispatcher/zipball/dbefd12671e8a14ec7f180cab83036ed26714bb0";
          sha256 = "05nicsd9lwl467bsv4sn44fjnnvqvzj1xqw2mmz9bac9zm66fsjd";
        };
      };
    };
    "psr/http-client" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "psr-http-client-0955afe48220520692d2d09f7ab7e0f93ffd6a31";
        src = fetchurl {
          url = "https://api.github.com/repos/php-fig/http-client/zipball/0955afe48220520692d2d09f7ab7e0f93ffd6a31";
          sha256 = "09r970lfpwil861gzm47446ck1s6km6ijibkxl13p1ymwdchnv6m";
        };
      };
    };
    "psr/http-factory" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "psr-http-factory-e616d01114759c4c489f93b099585439f795fe35";
        src = fetchurl {
          url = "https://api.github.com/repos/php-fig/http-factory/zipball/e616d01114759c4c489f93b099585439f795fe35";
          sha256 = "1vzimn3h01lfz0jx0lh3cy9whr3kdh103m1fw07qric4pnnz5kx8";
        };
      };
    };
    "psr/http-message" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "psr-http-message-402d35bcb92c70c026d1a6a9883f06b2ead23d71";
        src = fetchurl {
          url = "https://api.github.com/repos/php-fig/http-message/zipball/402d35bcb92c70c026d1a6a9883f06b2ead23d71";
          sha256 = "13cnlzrh344n00sgkrp5cgbkr8dznd99c3jfnpl0wg1fdv1x4qfm";
        };
      };
    };
    "psr/log" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "psr-log-fe5ea303b0887d5caefd3d431c3e61ad47037001";
        src = fetchurl {
          url = "https://api.github.com/repos/php-fig/log/zipball/fe5ea303b0887d5caefd3d431c3e61ad47037001";
          sha256 = "0a0rwg38vdkmal3nwsgx58z06qkfl85w2yvhbgwg45anr0b3bhmv";
        };
      };
    };
    "psr/simple-cache" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "psr-simple-cache-764e0b3939f5ca87cb904f570ef9be2d78a07865";
        src = fetchurl {
          url = "https://api.github.com/repos/php-fig/simple-cache/zipball/764e0b3939f5ca87cb904f570ef9be2d78a07865";
          sha256 = "0hgcanvd9gqwkaaaq41lh8fsfdraxmp2n611lvqv69jwm1iy76g8";
        };
      };
    };
    "psy/psysh" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "psy-psysh-0fa27040553d1d280a67a4393194df5228afea5b";
        src = fetchurl {
          url = "https://api.github.com/repos/bobthecow/psysh/zipball/0fa27040553d1d280a67a4393194df5228afea5b";
          sha256 = "1as5sp2vi6873in83qnxq6iiad9qd9zsladw4smid8nlvgkpajfz";
        };
      };
    };
    "ralouphie/getallheaders" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "ralouphie-getallheaders-120b605dfeb996808c31b6477290a714d356e822";
        src = fetchurl {
          url = "https://api.github.com/repos/ralouphie/getallheaders/zipball/120b605dfeb996808c31b6477290a714d356e822";
          sha256 = "1bv7ndkkankrqlr2b4kw7qp3fl0dxi6bp26bnim6dnlhavd6a0gg";
        };
      };
    };
    "ramsey/collection" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "ramsey-collection-a4b48764bfbb8f3a6a4d1aeb1a35bb5e9ecac4a5";
        src = fetchurl {
          url = "https://api.github.com/repos/ramsey/collection/zipball/a4b48764bfbb8f3a6a4d1aeb1a35bb5e9ecac4a5";
          sha256 = "0y5s9rbs023sw94yzvxr8fn9rr7xw03f08zmc9n9jl49zlr5s52p";
        };
      };
    };
    "ramsey/uuid" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "ramsey-uuid-60a4c63ab724854332900504274f6150ff26d286";
        src = fetchurl {
          url = "https://api.github.com/repos/ramsey/uuid/zipball/60a4c63ab724854332900504274f6150ff26d286";
          sha256 = "1w1i50pbd18awmvzqjkbszw79dl09912ibn95qm8lxr4nsjvbb27";
        };
      };
    };
    "revolution/laravel-mastodon-api" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "revolution-laravel-mastodon-api-b093f818d63f8da6a64114ce8ad2bcb636c40791";
        src = fetchurl {
          url = "https://api.github.com/repos/kawax/laravel-mastodon-api/zipball/b093f818d63f8da6a64114ce8ad2bcb636c40791";
          sha256 = "1ibh2mdcbi8w0gah85c5acwmviw2cwin5rjclf9v0ccnvjyb2mf4";
        };
      };
    };
    "romanzipp/laravel-queue-monitor" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "romanzipp-laravel-queue-monitor-fbd9142a92d6889378075fbeb2c3c70b25138b2d";
        src = fetchurl {
          url = "https://api.github.com/repos/romanzipp/Laravel-Queue-Monitor/zipball/fbd9142a92d6889378075fbeb2c3c70b25138b2d";
          sha256 = "103pv8dhw7m9yfqm3ic23dhdyc58dzjwa54kp23mszma61ib2lxx";
        };
      };
    };
    "sabberworm/php-css-parser" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "sabberworm-php-css-parser-e41d2140031d533348b2192a83f02d8dd8a71d30";
        src = fetchurl {
          url = "https://api.github.com/repos/sabberworm/PHP-CSS-Parser/zipball/e41d2140031d533348b2192a83f02d8dd8a71d30";
          sha256 = "0slqh0ra9cwk1pm4q7bqhndynir0yxypzrxb2vrfzfkmnh0rm02c";
        };
      };
    };
    "spatie/browsershot" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "spatie-browsershot-6503b2b429e10ff28a4cdb9fffaecc25ba6d032c";
        src = fetchurl {
          url = "https://api.github.com/repos/spatie/browsershot/zipball/6503b2b429e10ff28a4cdb9fffaecc25ba6d032c";
          sha256 = "10as5l75piw0ghp42lpxadzyd05gzxnnm6xzx1v949h4fsgaqkfj";
        };
      };
    };
    "spatie/crawler" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "spatie-crawler-f0f73947fdfaf68efdc68b73c4dbb28dfc785113";
        src = fetchurl {
          url = "https://api.github.com/repos/spatie/crawler/zipball/f0f73947fdfaf68efdc68b73c4dbb28dfc785113";
          sha256 = "1ya17399n84c2lszvv0srhm1zgng6mf2bz9k9s64n78izhhwr54y";
        };
      };
    };
    "spatie/enum" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "spatie-enum-f1a0f464ba909491a53e60a955ce84ad7cd93a2c";
        src = fetchurl {
          url = "https://api.github.com/repos/spatie/enum/zipball/f1a0f464ba909491a53e60a955ce84ad7cd93a2c";
          sha256 = "1501zvm71gnni3jwcrc9zn9zqwandar5mv540695r93py7h2dmlj";
        };
      };
    };
    "spatie/icalendar-generator" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "spatie-icalendar-generator-8af1ebe392d04369f647574d0053a00da4cff311";
        src = fetchurl {
          url = "https://api.github.com/repos/spatie/icalendar-generator/zipball/8af1ebe392d04369f647574d0053a00da4cff311";
          sha256 = "06ddmf9drbi3bm5yn30mk8z36s2zcgmwbdpx7gl39qhizbyzzp6i";
        };
      };
    };
    "spatie/image" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "spatie-image-2f802853aab017aa615224daae1588054b5ab20e";
        src = fetchurl {
          url = "https://api.github.com/repos/spatie/image/zipball/2f802853aab017aa615224daae1588054b5ab20e";
          sha256 = "137r2qxh0rfc79g427n3xpfipixmp3ibp10dqx5kmp1zwawl4g7x";
        };
      };
    };
    "spatie/image-optimizer" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "spatie-image-optimizer-af179994e2d2413e4b3ba2d348d06b4eaddbeb30";
        src = fetchurl {
          url = "https://api.github.com/repos/spatie/image-optimizer/zipball/af179994e2d2413e4b3ba2d348d06b4eaddbeb30";
          sha256 = "0cja2v23wvlyfrir5j13djgccc5xvi3z64i0p4zy6fsnxccxfhkj";
        };
      };
    };
    "spatie/laravel-package-tools" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "spatie-laravel-package-tools-cc7c991555a37f9fa6b814aa03af73f88026a83d";
        src = fetchurl {
          url = "https://api.github.com/repos/spatie/laravel-package-tools/zipball/cc7c991555a37f9fa6b814aa03af73f88026a83d";
          sha256 = "1xbyaizfvkcdrlpcs5ci30arnydckdga4a78xsfx8ylia606gcg4";
        };
      };
    };
    "spatie/laravel-sitemap" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "spatie-laravel-sitemap-39844ec1836e76f9f090075c49b5ae2b5fea79f9";
        src = fetchurl {
          url = "https://api.github.com/repos/spatie/laravel-sitemap/zipball/39844ec1836e76f9f090075c49b5ae2b5fea79f9";
          sha256 = "1ymg8cybcs71q0cp1bx7g9aklbaip87qsi7ijbd8hail580qnqz7";
        };
      };
    };
    "spatie/laravel-validation-rules" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "spatie-laravel-validation-rules-846b92dfbb76fd2a30c6011ae08082f0248c9cf4";
        src = fetchurl {
          url = "https://api.github.com/repos/spatie/laravel-validation-rules/zipball/846b92dfbb76fd2a30c6011ae08082f0248c9cf4";
          sha256 = "1k3ck6rzz8mfphvavsghasjzz6rwqf7vlys5cd31jvm2xnzh50aw";
        };
      };
    };
    "spatie/laravel-webhook-server" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "spatie-laravel-webhook-server-9d4506fb340db7750bcb088fe0253bdd30beaa32";
        src = fetchurl {
          url = "https://api.github.com/repos/spatie/laravel-webhook-server/zipball/9d4506fb340db7750bcb088fe0253bdd30beaa32";
          sha256 = "1a6fzdrf6qnh7b5avlapy4h19adl9hzisch7phf8mmidgn1h6nnk";
        };
      };
    };
    "spatie/robots-txt" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "spatie-robots-txt-f40a12b89f98dd18f3665673d04757298f5fbbf9";
        src = fetchurl {
          url = "https://api.github.com/repos/spatie/robots-txt/zipball/f40a12b89f98dd18f3665673d04757298f5fbbf9";
          sha256 = "03y7q1nflcdr9bl59vxdli0ca9fl3i7j20k204k4ns7myvgm4113";
        };
      };
    };
    "spatie/temporary-directory" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "spatie-temporary-directory-0c804873f6b4042aa8836839dca683c7d0f71831";
        src = fetchurl {
          url = "https://api.github.com/repos/spatie/temporary-directory/zipball/0c804873f6b4042aa8836839dca683c7d0f71831";
          sha256 = "007vxm2x1anrlnzwhrqyk4nw7yflaicfhr4q9wlp5bhy7d3ixljr";
        };
      };
    };
    "swagger-api/swagger-ui" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "swagger-api-swagger-ui-ded8d637ad6321abb41bfb68d110aa8012f6fa2b";
        src = fetchurl {
          url = "https://api.github.com/repos/swagger-api/swagger-ui/zipball/ded8d637ad6321abb41bfb68d110aa8012f6fa2b";
          sha256 = "1hv13803ybq97ijls05g5mz8kvrdn960xq6bb893fwaq0468x2nm";
        };
      };
    };
    "symfony/console" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-console-eca495f2ee845130855ddf1cf18460c38966c8b6";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/console/zipball/eca495f2ee845130855ddf1cf18460c38966c8b6";
          sha256 = "1z81pzshwbacrn77b90k0add0b5y6ll9j5nabpcwin41s1gqhy91";
        };
      };
    };
    "symfony/css-selector" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-css-selector-883d961421ab1709877c10ac99451632a3d6fa57";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/css-selector/zipball/883d961421ab1709877c10ac99451632a3d6fa57";
          sha256 = "0psdmi7lslpi4jqyk946cxrrzaylil6nnwv7zrwmdqsnchrb8i6b";
        };
      };
    };
    "symfony/deprecation-contracts" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-deprecation-contracts-7c3aff79d10325257a001fcf92d991f24fc967cf";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/deprecation-contracts/zipball/7c3aff79d10325257a001fcf92d991f24fc967cf";
          sha256 = "0p0c2942wjq1bb06y9i8gw6qqj7sin5v5xwsvl0zdgspbr7jk1m9";
        };
      };
    };
    "symfony/dom-crawler" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-dom-crawler-3fdd2a3d5fdc363b2e8dbf817f9726a4d013cbd1";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/dom-crawler/zipball/3fdd2a3d5fdc363b2e8dbf817f9726a4d013cbd1";
          sha256 = "1zagx4jzxjnhmgz2iwzazwmljirai3ql07f99i8gjv0pr11x6y28";
        };
      };
    };
    "symfony/error-handler" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-error-handler-85fd65ed295c4078367c784e8a5a6cee30348b7a";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/error-handler/zipball/85fd65ed295c4078367c784e8a5a6cee30348b7a";
          sha256 = "1mbxv8a5p8kk16505g20d0mh5k7ak6aqdhdckb1524wfj49r9g0y";
        };
      };
    };
    "symfony/event-dispatcher" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-event-dispatcher-adb01fe097a4ee930db9258a3cc906b5beb5cf2e";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/event-dispatcher/zipball/adb01fe097a4ee930db9258a3cc906b5beb5cf2e";
          sha256 = "0kgk5h0py9iyp924z3384cxpaf8qdjz8hcz52gqkjqcpmzf9hlag";
        };
      };
    };
    "symfony/event-dispatcher-contracts" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-event-dispatcher-contracts-a76aed96a42d2b521153fb382d418e30d18b59df";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/event-dispatcher-contracts/zipball/a76aed96a42d2b521153fb382d418e30d18b59df";
          sha256 = "1w49s1q6xhcmkgd3xkyjggiwys0wvyny0p3018anvdi0k86zg678";
        };
      };
    };
    "symfony/finder" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-finder-9915db259f67d21eefee768c1abcf1cc61b1fc9e";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/finder/zipball/9915db259f67d21eefee768c1abcf1cc61b1fc9e";
          sha256 = "0i92m2vcirf2i5whm5qgga6n0ls3z92b4d6qhdfnlz7q2jrj7rs2";
        };
      };
    };
    "symfony/http-foundation" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-http-foundation-cac1556fdfdf6719668181974104e6fcfa60e844";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/http-foundation/zipball/cac1556fdfdf6719668181974104e6fcfa60e844";
          sha256 = "0brad327jylwz4pfinl1118wax8mr2ka8k6qgl0dvlgwqk9g1zyx";
        };
      };
    };
    "symfony/http-kernel" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-http-kernel-36abb425b4af863ae1fe54d8a8b8b4c76a2bccdb";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/http-kernel/zipball/36abb425b4af863ae1fe54d8a8b8b4c76a2bccdb";
          sha256 = "0w6apkfi9a3p3y3akql8zv5w5ic7v7a5xqwk5dwch7lbjqi3yz44";
        };
      };
    };
    "symfony/mailer" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-mailer-7b03d9be1dea29bfec0a6c7b603f5072a4c97435";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/mailer/zipball/7b03d9be1dea29bfec0a6c7b603f5072a4c97435";
          sha256 = "1ml5dadsl2bvxf1pncdhk4xx11dwc940kj1m3l6rrw5w4ig4ny1h";
        };
      };
    };
    "symfony/mime" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-mime-9a0cbd52baa5ba5a5b1f0cacc59466f194730f98";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/mime/zipball/9a0cbd52baa5ba5a5b1f0cacc59466f194730f98";
          sha256 = "1srlfqj6bcpypmy0fm7rnd5q5qs94zgcg2gwypdjw4sw2kh0nvzk";
        };
      };
    };
    "symfony/polyfill-ctype" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-polyfill-ctype-ea208ce43cbb04af6867b4fdddb1bdbf84cc28cb";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/polyfill-ctype/zipball/ea208ce43cbb04af6867b4fdddb1bdbf84cc28cb";
          sha256 = "0ynkrpl3hb448dhab1injwwzfx68l75yn9zgc7lgqwbx60dvhqm3";
        };
      };
    };
    "symfony/polyfill-intl-grapheme" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-polyfill-intl-grapheme-875e90aeea2777b6f135677f618529449334a612";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/polyfill-intl-grapheme/zipball/875e90aeea2777b6f135677f618529449334a612";
          sha256 = "19j8qcbp525q7i61c2lhj6z2diysz45q06d990fvjby15cn0id0i";
        };
      };
    };
    "symfony/polyfill-intl-idn" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-polyfill-intl-idn-ecaafce9f77234a6a449d29e49267ba10499116d";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/polyfill-intl-idn/zipball/ecaafce9f77234a6a449d29e49267ba10499116d";
          sha256 = "0f42w4975rakhysnmhsyw6n3rjg6rjg7b7x8gs1n0qfdb6wc8m3q";
        };
      };
    };
    "symfony/polyfill-intl-normalizer" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-polyfill-intl-normalizer-8c4ad05dd0120b6a53c1ca374dca2ad0a1c4ed92";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/polyfill-intl-normalizer/zipball/8c4ad05dd0120b6a53c1ca374dca2ad0a1c4ed92";
          sha256 = "0msah2ii2174xh47v5x9vq1b1xn38yyx03sr3pa2rq3a849wi7nh";
        };
      };
    };
    "symfony/polyfill-mbstring" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-polyfill-mbstring-42292d99c55abe617799667f454222c54c60e229";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/polyfill-mbstring/zipball/42292d99c55abe617799667f454222c54c60e229";
          sha256 = "1m3l12y0lid3i0zy3m1jrk0z3zy8wpa7nij85zk2h5vbf924jnwa";
        };
      };
    };
    "symfony/polyfill-php72" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-polyfill-php72-70f4aebd92afca2f865444d30a4d2151c13c3179";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/polyfill-php72/zipball/70f4aebd92afca2f865444d30a4d2151c13c3179";
          sha256 = "10j5ipx16p6rybkpawqscpr2wcnby4270rbdj1qchr598wkvi0kb";
        };
      };
    };
    "symfony/polyfill-php80" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-polyfill-php80-6caa57379c4aec19c0a12a38b59b26487dcfe4b5";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/polyfill-php80/zipball/6caa57379c4aec19c0a12a38b59b26487dcfe4b5";
          sha256 = "05yfindyip9lbfr5apxkz6m0mlljrc9z6qylpxr6k5nkivlrcn9x";
        };
      };
    };
    "symfony/polyfill-php83" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-polyfill-php83-b0f46ebbeeeda3e9d2faebdfbf4b4eae9b59fa11";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/polyfill-php83/zipball/b0f46ebbeeeda3e9d2faebdfbf4b4eae9b59fa11";
          sha256 = "0z0xk1ghssa5qknp7cm3phdam77q4n46bkiwfpc5jkparkq958yb";
        };
      };
    };
    "symfony/polyfill-uuid" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-polyfill-uuid-9c44518a5aff8da565c8a55dbe85d2769e6f630e";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/polyfill-uuid/zipball/9c44518a5aff8da565c8a55dbe85d2769e6f630e";
          sha256 = "0w6mphwcz3n1qz0dc6nld5xqb179dvfcwys6r4nj4gjv5nm2nji0";
        };
      };
    };
    "symfony/process" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-process-0b5c29118f2e980d455d2e34a5659f4579847c54";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/process/zipball/0b5c29118f2e980d455d2e34a5659f4579847c54";
          sha256 = "09gy20j7wdwxdazm4ql04vfmyycfhs1r2d7f87ak09cip7iw0433";
        };
      };
    };
    "symfony/psr-http-message-bridge" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-psr-http-message-bridge-581ca6067eb62640de5ff08ee1ba6850a0ee472e";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/psr-http-message-bridge/zipball/581ca6067eb62640de5ff08ee1ba6850a0ee472e";
          sha256 = "1x9zyp5kmr1vdb457varl32bsr34j8ibcj1hd5kn5601wx6b1hf5";
        };
      };
    };
    "symfony/routing" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-routing-e7243039ab663822ff134fbc46099b5fdfa16f6a";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/routing/zipball/e7243039ab663822ff134fbc46099b5fdfa16f6a";
          sha256 = "0swarbjvhq2v29s4w97sx9dwhcm28q50b4wchd7119b8d13kppi7";
        };
      };
    };
    "symfony/service-contracts" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-service-contracts-40da9cc13ec349d9e4966ce18b5fbcd724ab10a4";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/service-contracts/zipball/40da9cc13ec349d9e4966ce18b5fbcd724ab10a4";
          sha256 = "188kncrgx16dg9x0ng47n4ljypblpxxn0bic5z75blihnydl5lb4";
        };
      };
    };
    "symfony/string" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-string-53d1a83225002635bca3482fcbf963001313fb68";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/string/zipball/53d1a83225002635bca3482fcbf963001313fb68";
          sha256 = "1hpwfygcgk262bpqc6p9y29b7jh5x9y1d5p8lyrnvd3vaig07f3j";
        };
      };
    };
    "symfony/translation" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-translation-3ed078c54bc98bbe4414e1e9b2d5e85ed5a5c8bd";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/translation/zipball/3ed078c54bc98bbe4414e1e9b2d5e85ed5a5c8bd";
          sha256 = "1s3xfzbg089m7nhl203wvnrvzi32yic6zvpymaw1g5xrcy0nl7yp";
        };
      };
    };
    "symfony/translation-contracts" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-translation-contracts-02c24deb352fb0d79db5486c0c79905a85e37e86";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/translation-contracts/zipball/02c24deb352fb0d79db5486c0c79905a85e37e86";
          sha256 = "1mpn6s7dv8q96pgg6f81gyvgdqrnmjg2g6g3x555s5qprmh4hliw";
        };
      };
    };
    "symfony/uid" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-uid-01b0f20b1351d997711c56f1638f7a8c3061e384";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/uid/zipball/01b0f20b1351d997711c56f1638f7a8c3061e384";
          sha256 = "0gmh5j09i4rfhkrbp47bm8620pfjqsh1yq02rcghblh9gkjqq81s";
        };
      };
    };
    "symfony/var-dumper" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-var-dumper-2027be14f8ae8eae999ceadebcda5b4909b81d45";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/var-dumper/zipball/2027be14f8ae8eae999ceadebcda5b4909b81d45";
          sha256 = "1sa9l4mhznhwsqb7i907qafjnc4vdw6b0pwnklsfp00zxkrp992s";
        };
      };
    };
    "symfony/yaml" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-yaml-e23292e8c07c85b971b44c1c4b87af52133e2add";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/yaml/zipball/e23292e8c07c85b971b44c1c4b87af52133e2add";
          sha256 = "1y8zi05h7gd5nmrrqac213a0h3nr4d51llip66d4hd5mkhfivv53";
        };
      };
    };
    "tijsverkoyen/css-to-inline-styles" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "tijsverkoyen-css-to-inline-styles-c42125b83a4fa63b187fdf29f9c93cb7733da30c";
        src = fetchurl {
          url = "https://api.github.com/repos/tijsverkoyen/CssToInlineStyles/zipball/c42125b83a4fa63b187fdf29f9c93cb7733da30c";
          sha256 = "0ckk04hwwz0fdkfr20i7xrhdjcnnw1b0liknbb81qyr1y4b7x3dd";
        };
      };
    };
    "trwl/socialite-mastodon" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "trwl-socialite-mastodon-3b79773cde1d319ade0b528e2d5a3cb998c40e5a";
        src = fetchurl {
          url = "https://api.github.com/repos/HerrLevin/socialite-mastodon/zipball/3b79773cde1d319ade0b528e2d5a3cb998c40e5a";
          sha256 = "03x0m8wv8r7sbnz1mrh9qrq01d8ycx92kayaicbdc68mns3af4ki";
        };
      };
    };
    "vlucas/phpdotenv" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "vlucas-phpdotenv-1a7ea2afc49c3ee6d87061f5a233e3a035d0eae7";
        src = fetchurl {
          url = "https://api.github.com/repos/vlucas/phpdotenv/zipball/1a7ea2afc49c3ee6d87061f5a233e3a035d0eae7";
          sha256 = "13h4xyxhdjn1n7xcxbcdhj20rv5fsaigbsbz61x2i224hj76620a";
        };
      };
    };
    "voku/portable-ascii" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "voku-portable-ascii-b56450eed252f6801410d810c8e1727224ae0743";
        src = fetchurl {
          url = "https://api.github.com/repos/voku/portable-ascii/zipball/b56450eed252f6801410d810c8e1727224ae0743";
          sha256 = "0gwlv1hr6ycrf8ik1pnvlwaac8cpm8sa1nf4d49s8rp4k2y5anyl";
        };
      };
    };
    "webmozart/assert" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "webmozart-assert-11cb2199493b2f8a3b53e7f19068fc6aac760991";
        src = fetchurl {
          url = "https://api.github.com/repos/webmozarts/assert/zipball/11cb2199493b2f8a3b53e7f19068fc6aac760991";
          sha256 = "18qiza1ynwxpi6731jx1w5qsgw98prld1lgvfk54z92b1nc7psix";
        };
      };
    };
    "zircote/swagger-php" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "zircote-swagger-php-f01574d1ac55cb0bf31a8dd80525ad58eef83afc";
        src = fetchurl {
          url = "https://api.github.com/repos/zircote/swagger-php/zipball/f01574d1ac55cb0bf31a8dd80525ad58eef83afc";
          sha256 = "1vcw6hrg8h5wyya7ac5fkir9d5ggb80bwdn6x8339nvv89i0iyyj";
        };
      };
    };
  };
  devPackages = {};
in
  composerEnv.buildPackage {
    inherit packages devPackages noDev;
    name = "traewelling";
    src = composerEnv.filterSrc ./.;
    executable = false;
    symlinkDependencies = false;
    meta = {
      license = "AGPL-3.0-only";
    };
  }
