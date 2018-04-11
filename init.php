<?php

SOMUSICAPI_CLASS_EventHandler::getInstance()->init();

$ROUTE  = '/smapi';

////////////////////////////////////////////////////////////////////////////////
//  APIs - SOCIAL NETWORK
////////////////////////////////////////////////////////////////////////////////

//User endpoints
OW::getRouter()->addRoute(new OW_Route('somusicapi.user.login',                                         $ROUTE.'/user/login',   'SOMUSICAPI_CTRL_User',    'login'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.user.logout',                                        $ROUTE.'/user/logout',  'SOMUSICAPI_CTRL_User',    'logout'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.user.signin',                                        $ROUTE.'/user/signin',  'SOMUSICAPI_CTRL_User',    'signin'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.user.userInfo',                                      $ROUTE.'/user/:id',     'SOMUSICAPI_CTRL_User',    'userInfo'));

//User list endpoints
OW::getRouter()->addRoute(new OW_Route('somusicapi.user-list.allUsers',                                 $ROUTE.'/user',                     'SOMUSICAPI_CTRL_UserList', 'allUsers'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.user-list.searchUser',                               $ROUTE.'/user/search/:realname',    'SOMUSICAPI_CTRL_UserList', 'searchUser'));

//Newsfeed endpoints
OW::getRouter()->addRoute(new OW_Route('somusicapi.social.newsfeed.addPost',                            $ROUTE.'/social/post/add',                          'SOMUSICAPI_CTRL_Newsfeed', 'addPost'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.social.newsfeed.deletePost',                         $ROUTE.'/social/post/:postId/delete',               'SOMUSICAPI_CTRL_Newsfeed', 'deletePost'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.social.newsfeed.like',                               $ROUTE.'/social/post/like',                         'SOMUSICAPI_CTRL_Newsfeed', 'like'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.social.newsfeed.unlike',                             $ROUTE.'/social/post/unlike',                       'SOMUSICAPI_CTRL_Newsfeed', 'unlike'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.social.newsfeed.allPosts',                           $ROUTE.'/social/post',                              'SOMUSICAPI_CTRL_Newsfeed', 'allPosts'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.social.newsfeed.getPostsByInterval',                 $ROUTE.'/social/post/interval/:index',              'SOMUSICAPI_CTRL_Newsfeed', 'getPostsByInterval'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.social.newsfeed.getUserPosts',                       $ROUTE.'/social/post/:userId',                      'SOMUSICAPI_CTRL_Newsfeed', 'getUserPosts'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.social.newsfeed.getPostsComposition',                $ROUTE.'/social/post/composition',                  'SOMUSICAPI_CTRL_Newsfeed', 'getPostsComposition'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.social.newsfeed.getScoreByPostId',                   $ROUTE.'/social/post/composition/:postId',          'SOMUSICAPI_CTRL_Newsfeed', 'getScoreByPostId'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.social.newsfeed.getLikesPost',                       $ROUTE.'/social/post/:postId/like',                 'SOMUSICAPI_CTRL_Newsfeed', 'getLikesPost'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.social.newsfeed.isLiked',                            $ROUTE.'/social/post/:postId/isliked',              'SOMUSICAPI_CTRL_Newsfeed', 'isLiked'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.social.newsfeed.addCommentPost',                     $ROUTE.'/social/post/comment/add',                  'SOMUSICAPI_CTRL_Newsfeed', 'addCommentPost'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.social.newsfeed.deleteCommentPost',                  $ROUTE.'/social/post/comment/delete/:commentId',    'SOMUSICAPI_CTRL_Newsfeed', 'deleteCommentPost'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.social.newsfeed.getCommentsPost',                    $ROUTE.'/social/post/comment/:idPost',              'SOMUSICAPI_CTRL_Newsfeed', 'getCommentsPost'));


//Friends endpoints
OW::getRouter()->addRoute(new OW_Route('somusicapi.social.friends.getFriends',                          $ROUTE.'/social/friend',                        'SOMUSICAPI_CTRL_Friends', 'getFriends'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.social.friends.isFriend',                            $ROUTE.'/social/friend/friendship/:userId',     'SOMUSICAPI_CTRL_Friends', 'isFriend'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.social.friends.accept',                              $ROUTE.'/social/friend/accept',                 'SOMUSICAPI_CTRL_Friends', 'accept'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.social.friends.request',                             $ROUTE.'/social/friend/request',                'SOMUSICAPI_CTRL_Friends', 'request'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.social.friends.ignore',                              $ROUTE.'/social/friend/ignore',                 'SOMUSICAPI_CTRL_Friends', 'ignore'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.social.friends.cancel',                              $ROUTE.'/social/friend/cancel',                 'SOMUSICAPI_CTRL_Friends', 'cancel'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.social.friends.delete',                              $ROUTE.'/social/friend/:friendId/delete',       'SOMUSICAPI_CTRL_Friends', 'delete'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.social.friends.findFriendship',                      $ROUTE.'/social/friend/status/info/:userId',    'SOMUSICAPI_CTRL_Friends', 'findFriendship'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.social.friends.getInfo',                             $ROUTE.'/social/friend/request/info/:request',  'SOMUSICAPI_CTRL_Friends', 'getInfo'));

//Notifications endpoints
OW::getRouter()->addRoute(new OW_Route('somusicapi.social.notifications.allNotificationsNotViewed',     $ROUTE.'/social/notifications/notviewed/:userId',   'SOMUSICAPI_CTRL_Notifications', 'allNotificationsNotViewed'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.social.notifications.allNotificationsViewed',        $ROUTE.'/social/notifications/viewed/:userId',      'SOMUSICAPI_CTRL_Notifications', 'allNotificationsViewed'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.social.notifications.setNotificationsAsViewed',      $ROUTE.'/social/notifications/mark',                'SOMUSICAPI_CTRL_Notifications', 'setNotificationsAsViewed'));


//Groups endpoints
OW::getRouter()->addRoute(new OW_Route('somusicapi.groups.allGroups',                                   $ROUTE.'/group',                                            'SOMUSICAPI_CTRL_Groups', 'allGroups'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.groups.create',                                      $ROUTE.'/group/create',                                     'SOMUSICAPI_CTRL_Groups', 'create'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.groups.delete',                                      $ROUTE.'/group/delete',                                     'SOMUSICAPI_CTRL_Groups', 'delete'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.groups.myGroupList',                                 $ROUTE.'/group/list',                                       'SOMUSICAPI_CTRL_Groups', 'myGroupList'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.groups.leave',                                       $ROUTE.'/group/leave',                                      'SOMUSICAPI_CTRL_Groups', 'leave'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.groups.latestList',                                  $ROUTE.'/group/latest',                                     'SOMUSICAPI_CTRL_Groups', 'latestList'));

OW::getRouter()->addRoute(new OW_Route('somusicapi.groups.myAdminGroupList',                            $ROUTE.'/group/list/admin',                                 'SOMUSICAPI_CTRL_Groups', 'myAdminGroupList'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.groups.myUserGroupList',                             $ROUTE.'/group/list/user',                                  'SOMUSICAPI_CTRL_Groups', 'myUserGroupList'));

OW::getRouter()->addRoute(new OW_Route('somusicapi.groups.userList',                                    $ROUTE.'/group/user/list/:groupId',                         'SOMUSICAPI_CTRL_Groups', 'userList'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.groups.join',                                        $ROUTE.'/group/user/join',                                  'SOMUSICAPI_CTRL_Groups', 'join'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.groups.invite',                                      $ROUTE.'/group/user/invite',                                'SOMUSICAPI_CTRL_Groups', 'invite'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.groups.declineInvite',                               $ROUTE.'/group/user/decline',                               'SOMUSICAPI_CTRL_Groups', 'declineInvite'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.groups.isJoin',                                      $ROUTE.'/group/user/isjoin',                                'SOMUSICAPI_CTRL_Groups', 'isJoin'));

OW::getRouter()->addRoute(new OW_Route('somusicapi.groups.inviteList',                                  $ROUTE.'/group/invite/list',                                'SOMUSICAPI_CTRL_Groups', 'inviteList'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.groups.invitationsLeft',                             $ROUTE.'/group/:groupId/invite/left',                       'SOMUSICAPI_CTRL_Groups', 'invitationsLeft'));

OW::getRouter()->addRoute(new OW_Route('somusicapi.groups.allPostsGroup',                               $ROUTE.'/group/post/:groupId',                              'SOMUSICAPI_CTRL_Groups', 'allPostsGroup'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.groups.deletePostInGroup',                           $ROUTE.'/group/post/:postId/delete',                        'SOMUSICAPI_CTRL_Groups', 'deletePostInGroup'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.groups.getPostsByIntervalInGroup',                   $ROUTE.'/group/post/:groupId/interval/:index',              'SOMUSICAPI_CTRL_Groups', 'getPostsByIntervalInGroup'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.groups.addPostInGroup',                              $ROUTE.'/group/post/add',                                   'SOMUSICAPI_CTRL_Groups', 'addPostInGroup'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.groups.addCommentPost',                              $ROUTE.'/group/post/comment/add',                           'SOMUSICAPI_CTRL_Groups', 'addCommentPost'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.groups.deleteCommentPost',                           $ROUTE.'/group/:groupId/post/comment/delete/:commentId',    'SOMUSICAPI_CTRL_Groups', 'deleteCommentPost'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.groups.getCommentsPost',                             $ROUTE.'/group/:groupId/post/comment/:postId',              'SOMUSICAPI_CTRL_Groups', 'getCommentsPost'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.groups.like',                                        $ROUTE.'/group/post/like',                                  'SOMUSICAPI_CTRL_Groups', 'like'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.groups.isLiked',                                     $ROUTE.'/group/:groupId/post/:postId/isliked',              'SOMUSICAPI_CTRL_Groups', 'isLiked'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.groups.getLikesPost',                                $ROUTE.'/group/:groupId/post/:postId/like',                 'SOMUSICAPI_CTRL_Groups', 'getLikesPost'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.groups.unlike',                                      $ROUTE.'/group/post/unlike',                                'SOMUSICAPI_CTRL_Groups', 'unlike'));

OW::getRouter()->addRoute(new OW_Route('somusicapi.groups.searchGroup',                                 $ROUTE.'/group/search/:groupname',                          'SOMUSICAPI_CTRL_Groups', 'searchGroup'));

//Push notifications endpoints
OW::getRouter()->addRoute(new OW_Route('somusicapi.push-manager.savePushToken',                         $ROUTE.'/push/save', 'SOMUSICAPI_CTRL_PushNotificationsManager', 'savePushToken'));


////////////////////////////////////////////////////////////////////////////////
//  APIs - SUPPORTO ALLA COMPOSIZIONE E ALLA DIDATTICA
////////////////////////////////////////////////////////////////////////////////


//Editor endpoints
OW::getRouter()->addRoute(new OW_Route('somusicapi.editor.deleteNotes',                                 $ROUTE.'/editor/note/delete',           'SOMUSICAPI_CTRL_Editor', 'deleteNotes'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.editor.addTie',                                      $ROUTE.'/editor/note/tie/add',          'SOMUSICAPI_CTRL_Editor', 'addTie'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.editor.addNote',                                     $ROUTE.'/editor/note/add',              'SOMUSICAPI_CTRL_Editor', 'addNote'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.editor.dotsUpdate',                                  $ROUTE.'/editor/note/dot',              'SOMUSICAPI_CTRL_Editor', 'dotsUpdate'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.editor.moveNotes',                                   $ROUTE.'/editor/note/move',             'SOMUSICAPI_CTRL_Editor', 'moveNotes'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.editor.setNoteAnnotationText',                       $ROUTE.'/editor/note/annotation',       'SOMUSICAPI_CTRL_Editor', 'setNoteAnnotationText'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.editor.changeNoteDuration',                          $ROUTE.'/editor/note/duration',         'SOMUSICAPI_CTRL_Editor', 'changeNoteDuration'));

OW::getRouter()->addRoute(new OW_Route('somusicapi.editor.getComposition',                              $ROUTE.'/editor/composition',           'SOMUSICAPI_CTRL_Editor', 'getJSONComposition'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.editor.getComposizionById',                          $ROUTE.'/editor/composition/:id',       'SOMUSICAPI_CTRL_Editor', 'getCompositionById'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.editor.setComposition',                              $ROUTE.'/editor/composition/set',       'SOMUSICAPI_CTRL_Editor', 'setCompositionAjax'));

OW::getRouter()->addRoute(new OW_Route('somusicapi.editor.removeInstrument',                            $ROUTE.'/editor/instrument/remove',     'SOMUSICAPI_CTRL_Editor', 'removeInstrument'));

OW::getRouter()->addRoute(new OW_Route('somusicapi.editor.accidentalUpdate',                            $ROUTE.'/editor/accidental',            'SOMUSICAPI_CTRL_Editor', 'accidentalUpdate'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.editor.close',                                       $ROUTE.'/editor/close',                 'SOMUSICAPI_CTRL_Editor', 'close'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.editor.exportMusicXML',                              $ROUTE.'/editor/exportxml',             'SOMUSICAPI_CTRL_Editor', 'exportMusicXML'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.editor.initEditor',                                  $ROUTE.'/editor/init',                  'SOMUSICAPI_CTRL_Editor', 'initEditor'));



//My Space endpoints
OW::getRouter()->addRoute(new OW_Route('somusicapi.myspace.addScore',                                   $ROUTE.'/myspace/addscore',             'SOMUSICAPI_CTRL_MySpace', 'addScore'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.myspace.removeScore',                                $ROUTE.'/myspace/removescore',          'SOMUSICAPI_CTRL_MySpace', 'removeScore'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.myspace.shareScore',                                 $ROUTE.'/myspace/sharescore',           'SOMUSICAPI_CTRL_MySpace', 'shareScore'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.myspace.getCompositions',                            $ROUTE.'/myspace/:idUser/composition',  'SOMUSICAPI_CTRL_MySpace', 'getCompositions'));


//Preview endpoints
OW::getRouter()->addRoute(new OW_Route('somusicapi.preview.importMusicXml',                             $ROUTE.'/preview/importmusicxml',   'SOMUSICAPI_CTRL_Preview', 'importMusicXML'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.preview.commitPreview',                              $ROUTE.'/preview/commitpreview',    'SOMUSICAPI_CTRL_Preview', 'commitPreview'));

//Instruments endpoints
OW::getRouter()->addRoute(new OW_Route('somusicapi.instruments.getInstruments',                         $ROUTE.'/instrument',       'SOMUSICAPI_CTRL_Instruments', 'getInstruments'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.instruments.getInstrumentsGroup',                    $ROUTE.'/instrumentgroup',  'SOMUSICAPI_CTRL_Instruments', 'getInstrumentsGroup'));


//Assignment manager endpoints
OW::getRouter()->addRoute(new OW_Route('somusicapi.assignment-manager.assignments',                     $ROUTE.'/assignment/:groupId',                          'SOMUSICAPI_CTRL_AssignmentManager', 'assignments'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.assignment-manager.allParticipantsAssignment',       $ROUTE.'/assignment/:assignmentId/participant',         'SOMUSICAPI_CTRL_AssignmentManager', 'allParticipantsAssignment'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.assignment-manager.newAssignment',                   $ROUTE.'/assignment/new',                               'SOMUSICAPI_CTRL_AssignmentManager', 'newAssignment'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.assignment-manager.saveNewAssignment',               $ROUTE.'/assignment/save',                              'SOMUSICAPI_CTRL_AssignmentManager', 'saveNewAssignment'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.assignment-manager.commitExecution',                 $ROUTE.'/assignment/commit',                            'SOMUSICAPI_CTRL_AssignmentManager', 'commitExecution'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.assignment-manager.editExecution',                   $ROUTE.'/assignment/edit',                              'SOMUSICAPI_CTRL_AssignmentManager', 'editExecution'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.assignment-manager.removeAssignment',                $ROUTE.'/assignment/remove',                            'SOMUSICAPI_CTRL_AssignmentManager', 'removeAssignment'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.assignment-manager.closeAssignment',                 $ROUTE.'/assignment/close',                             'SOMUSICAPI_CTRL_AssignmentManager', 'closeAssignment'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.assignment-manager.saveComment',                     $ROUTE.'/assignment/comment/save',                      'SOMUSICAPI_CTRL_AssignmentManager', 'saveComment'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.assignment-manager.completeAssignment',              $ROUTE.'/assignment/complete',                          'SOMUSICAPI_CTRL_AssignmentManager', 'completeAssignment'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.assignment-manager.makeCorrection',                  $ROUTE.'/assignment/correction',                        'SOMUSICAPI_CTRL_AssignmentManager', 'makeCorrection'));

OW::getRouter()->addRoute(new OW_Route('somusicapi.assignment-manager.getExecutionByAssignmentAndUser', $ROUTE.'/assignment/execution/:assignmentId/:userId',   'SOMUSICAPI_CTRL_AssignmentManager', 'getExecutionByAssignmentAndUser'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.assignment-manager.getExecutionsByAssignmentId',     $ROUTE.'/assignment/execution/:assignmentId',           'SOMUSICAPI_CTRL_AssignmentManager', 'getExecutionsByAssignmentId'));

OW::getRouter()->addRoute(new OW_Route('somusicapi.assignment-manager.getAssignmentById',               $ROUTE.'/:assignmentId/assignment',                     'SOMUSICAPI_CTRL_AssignmentManager', 'getAssignmentById'));



////////////////////////////////////////////////////////////////////////////////
//  OTHER ENDPOINTS
////////////////////////////////////////////////////////////////////////////////


//Instruments table endpoints
OW::getRouter()->addRoute(new OW_Route('somusicapi.instruments-table.addInstrument',                    $ROUTE.'/instrumentstable/addinstrument',                   'SOMUSIC_CTRL_InstrumentsTable', 'addInstrument'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.instruments-table.deleteInstrument',                 $ROUTE.'/instrumentstable/deleteinstrument',                'SOMUSIC_CTRL_InstrumentsTable', 'deleteInstrument'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.instruments-table.changeType',                       $ROUTE.'/instrumentstable/changetype',                      'SOMUSIC_CTRL_InstrumentsTable', 'changeType'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.instruments-table.changeUser',                       $ROUTE.'/instrumentstable/changeuser',                      'SOMUSIC_CTRL_InstrumentsTable', 'changeUser'));
OW::getRouter()->addRoute(new OW_Route('somusicapi.instruments-table.changeName',                       $ROUTE.'/instrumentstable/changename',                      'SOMUSIC_CTRL_InstrumentsTable', 'changeName'));
