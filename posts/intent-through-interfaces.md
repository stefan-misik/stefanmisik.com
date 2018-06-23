# Use of Interfaces to Express Intent
- published: 2018-08-03T20:00:00+0000
- tags: cpp, programming

Let's say we have two classes `remote_control` and `television`. One way to keep
information about which TV is the remote controlling is to provide the remote
with a pointer to the TV instance. We can reasonably assume that `television`,
however, has many more methods than just `receive_from_remote()`, which is
actually what `remote_control` is interested in. In this case it seems a good
idea to wrap `receive_from_remote()` into an interface:

```CPP
class remote_controllable
{
public:
    virtual void receive_from_remote(remote_command & cmd) = 0;
};
```

One can argue this makes sense even in case the `remote_control` is not an
universal one, and thus can only control the `television`. This is merely to
express an intent, behind `remote_control` holding a pointer to `television`
object instance. Moreover, if this pattern is kept for all reasonable sets of
functionalities, one can get good idea about a class just by looking at first
lines of class definition:

```CPP
class television:
    public remote_controllable
{
};
```

